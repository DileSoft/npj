<?php
/**
 * Auto-generated class. CSS syntax highlighting
 *
 * PHP version 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @link       http://pear.php.net/package/Text_Highlighter
 * @category   Text
 * @package Text_Highlighter
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 *
 */

/**
 * @ignore
 */

/**
 * Auto-generated class. CSS syntax highlighting
 *
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_CSS extends Text_Highlighter
{    /**
     * PHP4 Compatible Constructor
     *
     * @param array  $options
     * @access public
     */
    function Text_Highlighter_CSS($options=array())
    {
        $this->__construct($options);
    }


    /**
     *  Constructor
     *
     * @param array  $options
     * @access public
     */
    function __construct($options=array())
    {
        $this->_syntax = array (
            'keywords' => 
            array (
                'propertyValue' => 
                array (
                    'name' => 'propertyValue',
                    'innerGroup' => 'string',
                    'case' => false,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'far-left' => true,
                        'left' => true,
                        'center-left' => true,
                        'center-right' => true,
                        'center' => true,
                        'far-right' => true,
                        'right-side' => true,
                        'right' => true,
                        'behind' => true,
                        'leftwards' => true,
                        'rightwards' => true,
                        'inherit' => true,
                        'scroll' => true,
                        'fixed' => true,
                        'transparent' => true,
                        'none' => true,
                        'repeat-x' => true,
                        'repeat-y' => true,
                        'repeat' => true,
                        'no-repeat' => true,
                        'collapse' => true,
                        'separate' => true,
                        'auto' => true,
                        'top' => true,
                        'bottom' => true,
                        'both' => true,
                        'open-quote' => true,
                        'close-quote' => true,
                        'no-open-quote' => true,
                        'no-close-quote' => true,
                        'crosshair' => true,
                        'default' => true,
                        'pointer' => true,
                        'move' => true,
                        'e-resize' => true,
                        'ne-resize' => true,
                        'nw-resize' => true,
                        'n-resize' => true,
                        'se-resize' => true,
                        'sw-resize' => true,
                        's-resize' => true,
                        'text' => true,
                        'wait' => true,
                        'help' => true,
                        'ltr' => true,
                        'rtl' => true,
                        'inline' => true,
                        'block' => true,
                        'list-item' => true,
                        'run-in' => true,
                        'compact' => true,
                        'marker' => true,
                        'table' => true,
                        'inline-table' => true,
                        'table-row-group' => true,
                        'table-header-group' => true,
                        'table-footer-group' => true,
                        'table-row' => true,
                        'table-column-group' => true,
                        'table-column' => true,
                        'table-cell' => true,
                        'table-caption' => true,
                        'below' => true,
                        'level' => true,
                        'above' => true,
                        'higher' => true,
                        'lower' => true,
                        'show' => true,
                        'hide' => true,
                        'caption' => true,
                        'icon' => true,
                        'menu' => true,
                        'message-box' => true,
                        'small-caption' => true,
                        'status-bar' => true,
                        'normal' => true,
                        'wider' => true,
                        'narrower' => true,
                        'ultra-condensed' => true,
                        'extra-condensed' => true,
                        'condensed' => true,
                        'semi-condensed' => true,
                        'semi-expanded' => true,
                        'expanded' => true,
                        'extra-expanded' => true,
                        'ultra-expanded' => true,
                        'italic' => true,
                        'oblique' => true,
                        'small-caps' => true,
                        'bold' => true,
                        'bolder' => true,
                        'lighter' => true,
                        'inside' => true,
                        'outside' => true,
                        'disc' => true,
                        'circle' => true,
                        'square' => true,
                        'decimal' => true,
                        'decimal-leading-zero' => true,
                        'lower-roman' => true,
                        'upper-roman' => true,
                        'lower-greek' => true,
                        'lower-alpha' => true,
                        'lower-latin' => true,
                        'upper-alpha' => true,
                        'upper-latin' => true,
                        'hebrew' => true,
                        'armenian' => true,
                        'georgian' => true,
                        'cjk-ideographic' => true,
                        'hiragana' => true,
                        'katakana' => true,
                        'hiragana-iroha' => true,
                        'katakana-iroha' => true,
                        'crop' => true,
                        'cross' => true,
                        'invert' => true,
                        'visible' => true,
                        'hidden' => true,
                        'always' => true,
                        'avoid' => true,
                        'x-low' => true,
                        'low' => true,
                        'medium' => true,
                        'high' => true,
                        'x-high' => true,
                        'mix?' => true,
                        'repeat?' => true,
                        'static' => true,
                        'relative' => true,
                        'absolute' => true,
                        'portrait' => true,
                        'landscape' => true,
                        'spell-out' => true,
                        'once' => true,
                        'digits' => true,
                        'continuous' => true,
                        'code' => true,
                        'x-slow' => true,
                        'slow' => true,
                        'fast' => true,
                        'x-fast' => true,
                        'faster' => true,
                        'slower' => true,
                        'justify' => true,
                        'underline' => true,
                        'overline' => true,
                        'line-through' => true,
                        'blink' => true,
                        'capitalize' => true,
                        'uppercase' => true,
                        'lowercase' => true,
                        'embed' => true,
                        'bidi-override' => true,
                        'baseline' => true,
                        'sub' => true,
                        'super' => true,
                        'text-top' => true,
                        'middle' => true,
                        'text-bottom' => true,
                        'silent' => true,
                        'x-soft' => true,
                        'soft' => true,
                        'loud' => true,
                        'x-loud' => true,
                        'pre' => true,
                        'nowrap' => true,
                        'serif' => true,
                        'sans-serif' => true,
                        'cursive' => true,
                        'fantasy' => true,
                        'monospace' => true,
                        'empty' => true,
                        'string' => true,
                        'strict' => true,
                        'loose' => true,
                        'char' => true,
                        'true' => true,
                        'false' => true,
                        'dotted' => true,
                        'dashed' => true,
                        'solid' => true,
                        'double' => true,
                        'groove' => true,
                        'ridge' => true,
                        'inset' => true,
                        'outset' => true,
                        'larger' => true,
                        'smaller' => true,
                        'xx-small' => true,
                        'x-small' => true,
                        'small' => true,
                        'large' => true,
                        'x-large' => true,
                        'xx-large' => true,
                        'all' => true,
                        'newspaper' => true,
                        'distribute' => true,
                        'distribute-all-lines' => true,
                        'distribute-center-last' => true,
                        'inter-word' => true,
                        'inter-ideograph' => true,
                        'inter-cluster' => true,
                        'kashida' => true,
                        'ideograph-alpha' => true,
                        'ideograph-numeric' => true,
                        'ideograph-parenthesis' => true,
                        'ideograph-space' => true,
                        'keep-all' => true,
                        'break-all' => true,
                        'break-word' => true,
                        'lr-tb' => true,
                        'tb-rl' => true,
                        'thin' => true,
                        'thick' => true,
                        'inline-block' => true,
                        'w-resize' => true,
                        'hand' => true,
                        'distribute-letter' => true,
                        'distribute-space' => true,
                        'whitespace' => true,
                        'male' => true,
                        'female' => true,
                        'child' => true,
                    ),
                ),
                'namedcolor' => 
                array (
                    'name' => 'namedcolor',
                    'innerGroup' => 'var',
                    'case' => false,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'aqua' => true,
                        'black' => true,
                        'blue' => true,
                        'fuchsia' => true,
                        'gray' => true,
                        'green' => true,
                        'lime' => true,
                        'maroon' => true,
                        'navy' => true,
                        'olive' => true,
                        'purple' => true,
                        'red' => true,
                        'silver' => true,
                        'teal' => true,
                        'white' => true,
                        'yellow' => true,
                        'activeborder' => true,
                        'activecaption' => true,
                        'appworkspace' => true,
                        'background' => true,
                        'buttonface' => true,
                        'buttonhighlight' => true,
                        'buttonshadow' => true,
                        'buttontext' => true,
                        'captiontext' => true,
                        'graytext' => true,
                        'highlight' => true,
                        'highlighttext' => true,
                        'inactiveborder' => true,
                        'inactivecaption' => true,
                        'inactivecaptiontext' => true,
                        'infobackground' => true,
                        'infotext' => true,
                        'menu' => true,
                        'menutext' => true,
                        'scrollbar' => true,
                        'threeddarkshadow' => true,
                        'threedface' => true,
                        'threedhighlight' => true,
                        'threedlightshadow' => true,
                        'threedshadow' => true,
                        'window' => true,
                        'windowframe' => true,
                        'windowtext' => true,
                    ),
                ),
            ),
            'blocks' => 
            array (
                'atrule' => 
                array (
                    'name' => 'atrule',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/(@[a-z\\d]+)/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 0,
                ),
                'property' => 
                array (
                    'name' => 'property',
                    'case' => false,
                    'innerGroup' => 'code',
                    'delimGroup' => 'reserved',
                    'start' => '/[a-z][a-z\\d\\-]*\\s*:/i',
                    'end' => '/(?=;|\\})/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 1,
                    'lookfor' => 
                    array (
                        0 => 'measure',
                        1 => 'number',
                        2 => 'identifier',
                        3 => 'hexcolor',
                    ),
                ),
                'selector' => 
                array (
                    'name' => 'selector',
                    'case' => false,
                    'innerGroup' => 'identifier',
                    'match' => '/(((\\.|#)?[a-z]+[a-z\\d\\-]*(?![a-z\\d\\-]))|(\\*))(?!\\s*:\\s*[\\s\\{])/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 2,
                ),
                'pseudo' => 
                array (
                    'name' => 'pseudo',
                    'case' => false,
                    'innerGroup' => 'special',
                    'match' => '/:[a-z][a-z\\d\\-]*/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 3,
                ),
                'bescaped' => 
                array (
                    'name' => 'bescaped',
                    'case' => false,
                    'innerGroup' => 'string',
                    'match' => '/\\\\[\\\\(\\\\)\\\\]/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 4,
                ),
                'paramselector' => 
                array (
                    'name' => 'paramselector',
                    'case' => false,
                    'innerGroup' => 'code',
                    'delimGroup' => 'brackets',
                    'start' => '/\\[/i',
                    'end' => '/\\]/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 5,
                    'lookfor' => 
                    array (
                        0 => 'strsingle',
                        1 => 'strdouble',
                        2 => 'paramname',
                    ),
                ),
                'block' => 
                array (
                    'name' => 'block',
                    'case' => false,
                    'innerGroup' => 'code',
                    'delimGroup' => 'brackets',
                    'start' => '/\\{/i',
                    'end' => '/\\}/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 6,
                    'lookfor' => 
                    array (
                        0 => 'property',
                        1 => 'selector',
                        2 => 'block',
                    ),
                ),
                'brackets' => 
                array (
                    'name' => 'brackets',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'brackets',
                    'start' => '/\\(/i',
                    'end' => '/\\)/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 7,
                    'lookfor' => 
                    array (
                        0 => 'bescaped',
                    ),
                ),
                'strsingle' => 
                array (
                    'name' => 'strsingle',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/\'/i',
                    'end' => '/\'/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 8,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
                    ),
                ),
                'escaped' => 
                array (
                    'name' => 'escaped',
                    'case' => false,
                    'innerGroup' => 'special',
                    'match' => '/\\\\\\\\|\\\\"|\\\\\'|\\\\`/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 9,
                ),
                'descaped' => 
                array (
                    'name' => 'descaped',
                    'case' => false,
                    'innerGroup' => 'special',
                    'match' => '/\\\\\\\\|\\\\"|\\\\\'|\\\\`|\\\\t|\\\\n|\\\\r/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 10,
                ),
                'strdouble' => 
                array (
                    'name' => 'strdouble',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/"/i',
                    'end' => '/"/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 11,
                    'lookfor' => 
                    array (
                        0 => 'descaped',
                    ),
                ),
                'measure' => 
                array (
                    'name' => 'measure',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/\\d*\\.?\\d+(\\%|em|ex|pc|pt|px|in|mm|cm)/i',
                    'multiline' => false,
                    'partClass' => 
                    array (
                        1 => 'string',
                    ),
                    'type' => 'block',
                    'order' => 12,
                ),
                'number' => 
                array (
                    'name' => 'number',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/\\d*\\.?\\d+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 13,
                ),
                'identifier' => 
                array (
                    'name' => 'identifier',
                    'case' => false,
                    'innerGroup' => 'code',
                    'match' => '/[a-z][a-z\\d\\-]*/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 14,
                ),
                'hexcolor' => 
                array (
                    'name' => 'hexcolor',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/#([\\da-f]{6}|[\\da-f]{3})\\b/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 15,
                ),
                'paramname' => 
                array (
                    'name' => 'paramname',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/[\\w\\-\\:]+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 16,
                ),
            ),
            'toplevel' => 
            array (
                    0 => 'atrule',
                    1 => 'selector',
                    2 => 'pseudo',
                3 => 'paramselector',
                4 => 'block',
            ),
            'case' => false,
            'defClass' => 'code',
        );

        $this->_options = $options;
    }
}

?>
