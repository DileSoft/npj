<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Highlighter base class
 *
 * PHP versions 4 and 5
 * 
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Text
 * @package Text_Highlighter
 * @author     Andrey Demenev <demenev@on-line.jar.ru>
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @link       http://pear.php.net/package/Text_Highlighter
 */

include_once(dirname(__FILE__).'/Highlighter/Renderer.php');

// {{{ BC constants

// BC trick : define constants related to default
// renderer if needed
if (!defined('HL_NUMBERS_LI')) {
/**#@+
 * Constant for use with $options['numbers']
 * @see Text_Highlighter_Renderer_Html::_init()
 */
/**
 * use numbered list
 */
define ('HL_NUMBERS_LI'    ,    1);
/**
 * Use 2-column table with line numbers in left column and code in  right column.
 * Forces $options['tag'] = HL_TAG_PRE
 */
define ('HL_NUMBERS_TABLE'    , 2);
/**#@-*/
}

// }}}
// {{{ constants
/**
 * for our purpose, it is infinity
 */
define ('HL_INFINITY',      1000000000);

// }}}

/**
 * Text highlighter base class
 *
 * @author     Andrey Demenev <demenev@on-line.jar.ru>
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */

// {{{ Text_Highlighter

/**
 * Text highlighter base class
 *
 * This class implements all functions necessary for highlighting,
 * but it does not contain highlighting rules. Actual highlighting is
 * done using a descendent of this class.
 *
 * One is not supposed to manually create descendent classes.
 * Instead, describe highlighting  rules in XML format and
 * use {@link Text_Highlighter_Generator} to create descendent class.
 * Alternatively, an instance of a descendent class can be created
 * directly.
 *
 * Use {@link Text_Highlighter::factory()} to create an
 * object for particular language highlighter
 *
 * Usage example
 * <code>
 *require_once 'Text/Highlighter.php';
 *$hlSQL =& Text_Highlighter::factory('SQL',array('numbers'=>true));
 *echo $hlSQL->highlight('SELECT * FROM table a WHERE id = 12');
 * </code>
 *
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @package Text_Highlighter
 * @access public
 */

class Text_Highlighter
{
    // {{{ members

    /**
     * Syntax highlighting rules.
     * Auto-generated classes set this var
     *
     * @access protected
     * @see _init
     * @var array
     */
    var $_syntax;

    /**
     * Renderer object.
     *
     * @access private
     * @var array
     */
    var $_renderer;

    /**
     * Options. Keeped for BC
     *
     * @access protected
     * @var array
     */
    var $_options = array();

    // }}}
    // {{{ factory

    /**
     * Create a new Highlighter object for specified language
     *
     * @param string $lang    language, for example "SQL"
     * @param array  $options Rendering options. This
     * parameter is only keeped for BC reasons, use 
     * {@link Text_Highlighter::setRenderer()} instead
     *
     * @return mixed a newly created Highlighter object, or 
     * a PEAR error object on error
     *
     * @static
     * @access public
     */
    function &factory($lang, $options = array())
    {
        $lang = strtoupper($lang);
        @include_once (dirname(__FILE__).'/Highlighter/' . $lang . '.php');

        $classname = 'Text_Highlighter_' . $lang;

        if (!class_exists($classname)) {
            return 'Highlighter for ' . $lang . ' not found';
        }

        $obj =& new $classname($options);

        return $obj;
    }

    // }}}
    // {{{ setRenderer
    
    /**
     * Set renderer object
     *
     * @param object $renderer  Text_Highlighter_Renderer
     *
     * @access public
     */
    function setRenderer(&$renderer)
    {
        $this->_renderer =& $renderer;
        }

    // }}}

    /**
     * Helper function to find matching brackets
     *
     * @access private
     */
    function _matchingBrackets($str)
    {
        return strtr($str, '()<>[]{}', ')(><][}{');
    }

    // {{{ highlight

    /**
     * Highlights code
     *
     * @param  string $str Code to highlight
     * @access public
     * @return string Highlighted text
     *
     */
    function highlight($str)
    {
        if (!($this->_renderer)) {
            include_once(dirname(__FILE__).'/Highlighter/Renderer/Html.php');
            $this->_renderer =& new Text_Highlighter_Renderer_Html($this->_options);
        }
        $this->_renderer->reset();
        $str = $this->_renderer->preprocess($str);

        $this->_lastMatch = '';
        // current position in string
        $pos = 0;
        // nested regions stack
        $stack = array();

        // current region
        $current = NULL;
        
        // what to seek first
        $blocksToSeek = isset($this->_syntax['toplevel']) ? 
                          $this->_syntax['toplevel'] :
                          null;

        $defClass=$this->_syntax['defClass'];
        $matches = null;
        while (true) {
            // init loop vars
            $matchpos = HL_INFINITY;
            $matchlen = 0;
            $what = -1;
            $thematch = null;
            $theregion = null;
            
            // get rid of the chars already processed
            $substr = substr($str, $pos);
            if ($substr === false) break;

            $firstline = $substr;
            
            
            // look for blocks, either top-level or
            // allowed within current region
            if ($matchpos && $blocksToSeek) {
                foreach ($blocksToSeek as $region) {
                    $region = $this->_syntax['blocks'][$region];
                    if ($region['type'] == 'block') {
                    // {{{ blocks search
                    if ($current) {
                            $defClass = $current['innerGroup'];
                    }
                        if (preg_match($region['match'], $substr, $matches, PREG_OFFSET_CAPTURE) &&
                            $matchpos > $matches[0][1]) {
                            if (isset($region['BOL']) && ($pos || $matches[0][1]) && $substr{$matches[0][1]-1} != "\n") {
                                continue;
                            }
                            if (isset($region['neverafter']) && !$matches[0][1] && preg_match($region['neverafter'], $this->_lastMatch)) {
                                continue;
                            }
                            if (isset($region['neverafter']) && $matches[0][1] && preg_match($region['neverafter'], $this->_lastMatch . substr($substr, 0, $matches[0][1]))) {
                                continue;
                            }
                        $matchlen = strlen($matches[0][0]);
                        $matchpos = $matches[0][1];
                        $thematch = $matches[0][0];
                        $thematches = $matches;
                        $what     = 1;
                        $theregion = $region;
                        if (!$region['multiline']) {
                            // if found a block, and it is not multi-line
                            // then remove all after that line from the subject string
                            $newlinePos = strpos($firstline, "\n", $matchpos);
                            if ($newlinePos) {
                                $firstline = substr($firstline, 0, $newlinePos);
                            }
                        }
                    }
                    if (!$matchpos) {
                        break;
                    }
                    // }}}
                    } else {
                    // {{{ start of region search
                    if ($current) {
                            $defClass=$current['innerGroup'];
                    }
                    if (preg_match($region['start'], $substr, $matches, PREG_OFFSET_CAPTURE) &&
                            $matchpos > $matches[0][1]) {
                            if (isset($region['startBOL']) && ($pos || $matches[0][1]) && $str{$pos + $matches[0][1]-1} != "\n") {
                                continue;
                            }
                            if (isset($region['neverafter']) && !$matches[0][1] && preg_match($region['neverafter'], $this->_lastMatch)) {
                                continue;
                            }
                            if (isset($region['neverafter']) && $matches[0][1] && preg_match($region['neverafter'], $this->_lastMatch . substr($substr, 0 ,$matches[0][1]))) {
                                continue;
                            }
                        $matchlen  = strlen($matches[0][0]);
                        $matchpos  = $matches[0][1];
                        $thematch  = $matches[0][0];
                        $what      = 0;
                        if ($region['remember']) {
                            foreach ($matches as $i => $amatch) {
                                    $quoted = preg_quote($amatch[0], '/');
                                $region['end'] = str_replace('%'.$i.'%', $quoted, $region['end']);
                                    $region['end'] = str_replace('%b'.$i.'%', $this->_matchingBrackets($quoted), $region['end']);
                            }
                        }
                        $theregion = $region;
                    }
                    if (!$matchpos) {
                        break;
                    }
                    // }}}
                }
            }
            }
            // {{{ end of region search

            // look for end of region
            if ($matchpos &&
                    $current && 
                    preg_match($current['end'], $substr, $matches, PREG_OFFSET_CAPTURE) &&
                    $matchpos > $matches[0][1]) {
                if (isset($region['endBOL']) && ($pos || $matches[0][1]) && $str{$pos + $matches[0][1]-1} != "\n") {
                } else {
                $matchlen  = strlen($matches[0][0]);
                $matchpos  = $matches[0][1];
                $thematch  = $matches[0][0];
                $what      = 2;
                $theregion = $region;
            }
            }

            // }}}
            switch ($what) {
                // found start of region
                case 0:
                    if ($matchpos) {
                        $this->_renderer->acceptToken($defClass, substr($substr, 0, $matchpos));
                    }
                    $this->_renderer->acceptToken($theregion['delimGroup'], $thematch);
                    if ($current) {
                        array_push($stack, $current);
                    }
                    $current = $theregion;
                    $blocksToSeek = isset($current['lookfor']) ? 
                                       $current['lookfor'] :
                                       null;
/*                    $regionsToSeek = isset($current['lookfor']['regions']) ? 
                                       $current['lookfor']['regions'] :
                                       null;
                    $blocksToSeek = array_merge((array)$blocksToSeek, (array)$regionsToSeek); */
                    $pos += $matchpos + $matchlen;
                    $defClass = $current['innerGroup'];
                    break;

                // found a block
                case 1:
                    if ($matchpos) {
                        $this->_renderer->acceptToken($defClass, substr($substr, 0, $matchpos));
                    }
                    if (isset($theregion['partClass'])) {
                        $partpos = $matchpos;
                        $nparts=count($thematches);
                        for ($i=1; $i<$nparts; $i++) {
                            if ($thematches[$i][1] < 0) {
                                continue;
                            }
                            if (isset($theregion['partClass'][$i])) {
                                if ($thematches[$i][1] > $partpos) {
                                    $this->_renderer->acceptToken($class, substr($substr, $partpos, $thematches[$i][1]-$partpos));
                                }
                                $this->_renderer->acceptToken($theregion['partClass'][$i], $thematches[$i][0]);
                            }
                            $partpos = $thematches[$i][1] + strlen($thematches[$i][0]);
                        }
                        if ($partpos < $matchpos + $matchlen) {
                            $this->_renderer->acceptToken($class, substr($substr, $partpos, $matchlen - $partpos + $matchpos));
                        }
                    } else {
                        while (true) {
                            $newregion = null;
                            if (isset($theregion['innerGroup'])) {
                                $class = $theregion['innerGroup'];
                            } else {
                                $class = $defClass;
                            }
                            foreach ((array)$this->_syntax['keywords'] as $kwgroup) {
                                if ($kwgroup['inherits'] == $theregion['name']) {
                                    $csmatch = $kwgroup['case'] ? $thematch : strtolower($thematch);
                                    if (isset($kwgroup['match'][$csmatch])) {
                                        $class = $kwgroup['innerGroup'];
                                        $newregion = null;
                                        break;
                                    }
                                    if (isset($kwgroup['otherwise'])) {
                                        $newregion = $this->_syntax['blocks'][$kwgroup['otherwise']];
                                    }
                                }
                            }
                            if ($newregion) {
                                $theregion = $newregion;
                                continue;
                            }
                            break;
                        }
                        $this->_renderer->acceptToken($class, $thematch);
                    }
                    $pos += $matchpos + $matchlen;
                    break;

                // found end of region
                case 2:
                    if ($matchpos) {
                        $this->_renderer->acceptToken($current['innerGroup'], substr($substr, 0, $matchpos));
                    }
                    $pos += $matchpos + $matchlen;
                    $this->_renderer->acceptToken($current['delimGroup'], $thematch);
                    $current = array_pop($stack);
                    if ($current) {
                        $blocksToSeek = isset($current['lookfor']) ? 
                            $current['lookfor'] :
                                       null;
                    } else {
                        $blocksToSeek = isset($this->_syntax['toplevel']) ? 
                            $this->_syntax['toplevel'] :
                                           null;
                         $defClass=$this->_syntax['defClass'];
                    }
                    break;
                default:
                    $this->_renderer->acceptToken($defClass, $substr);
                    $pos = HL_INFINITY;
            }
            $this->_lastMatch = $thematch;

        }
        if ($pos<strlen($substr)) {
            $this->_renderer->acceptToken($defClass, $substr);
            }
        $this->_renderer->finalize();
        return $this->_renderer->getOutput();
        }

    // }}}

    }

// }}}
    
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
     */

?>
