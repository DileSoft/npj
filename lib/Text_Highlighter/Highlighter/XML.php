<?php
/**
 * Auto-generated class. XML syntax highlighting 
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
 * @package    Text_Highlighter
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 *
 */

/**
 * @ignore
 */

/**
 * Auto-generated class. XML syntax highlighting
 *
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_XML extends Text_Highlighter
{    /**
     * PHP4 Compatible Constructor
     *
     * @param array  $options
     * @access public
     */
    function Text_Highlighter_XML($options=array())
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
            ),
            'blocks' => 
            array (
                'cdata' => 
                array (
                    'name' => 'cdata',
                    'case' => false,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'comment',
                    'start' => '/\\<\\!\\[CDATA\\[/i',
                    'end' => '/\\]\\]\\>/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 0,
                ),
                'comment' => 
                array (
                    'name' => 'comment',
                    'case' => false,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'comment',
                    'start' => '/\\<!--/i',
                    'end' => '/--\\>/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 1,
                ),
                'tag' => 
                array (
                    'name' => 'tag',
                    'case' => false,
                    'innerGroup' => 'code',
                    'delimGroup' => 'brackets',
                    'start' => '/\\<[\\?\\/]?/i',
                    'end' => '/[\\/\\?]?\\>/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 2,
                    'lookfor' => 
                    array (
                        0 => 'tagname',
                        1 => 'paramname',
                        2 => 'param',
                    ),
                ),
                'tagname' => 
                array (
                    'name' => 'tagname',
                    'case' => false,
                    'innerGroup' => 'reserved',
                    'match' => '/^[\\w\\-\\:]+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 3,
                ),
                'paramname' => 
                array (
                    'name' => 'paramname',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/[\\w\\-\\:]+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 4,
                ),
                'entity' => 
                array (
                    'name' => 'entity',
                    'case' => false,
                    'innerGroup' => 'special',
                    'match' => '/(&|%)[\\w\\-\\.]+;/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 5,
                ),
                'param' => 
                array (
                    'name' => 'param',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/"/i',
                    'end' => '/"/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 6,
                    'lookfor' => 
                    array (
                        0 => 'entity',
                    ),
                ),
            ),
            'toplevel' => 
            array (
                0 => 'cdata',
                1 => 'comment',
                2 => 'tag',
                3 => 'entity',
            ),
            'case' => false,
            'defClass' => 'code',
        );

        $this->_options = $options;
    }
}

?>
