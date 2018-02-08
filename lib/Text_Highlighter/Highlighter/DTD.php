<?php
/**
 * Auto-generated class. DTD syntax highlighting 
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
 * Auto-generated class. DTD syntax highlighting
 *
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_DTD extends Text_Highlighter
{    /**
     * PHP4 Compatible Constructor
     *
     * @param array  $options
     * @access public
     */
    function Text_Highlighter_DTD($options=array())
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
                'comment' => 
                array (
                    'name' => 'comment',
                    'case' => true,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'comment',
                    'start' => '/\\<!--/',
                    'end' => '/--\\>/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 0,
                ),
                'redecl' => 
                array (
                    'name' => 'redecl',
                    'case' => true,
                    'innerGroup' => 'code',
                    'delimGroup' => 'brackets',
                    'start' => '/\\<\\!\\[/',
                    'end' => '/\\]\\]\\>/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 1,
                    'lookfor' => 
                    array (
                        0 => 'comment',
                        1 => 'tag',
                        2 => 'pcdata',
                        3 => 'entity',
                        4 => 'identifier',
                    ),
                ),
                'tag' => 
                array (
                    'name' => 'tag',
                    'case' => true,
                    'innerGroup' => 'code',
                    'delimGroup' => 'brackets',
                    'start' => '/\\</',
                    'end' => '/\\>/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 2,
                    'lookfor' => 
                    array (
                        0 => 'comment',
                        1 => 'brackets',
                        2 => 'strsingle',
                        3 => 'strdouble',
                        4 => 'tagname',
                        5 => 'reserved',
                        6 => 'pcdata',
                        7 => 'entity',
                        8 => 'identifier',
                    ),
                ),
                'brackets' => 
                array (
                    'name' => 'brackets',
                    'case' => true,
                    'innerGroup' => 'code',
                    'delimGroup' => 'brackets',
                    'start' => '/\\(/',
                    'end' => '/\\)/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 3,
                    'lookfor' => 
                    array (
                        0 => 'entity',
                        1 => 'identifier',
                    ),
                ),
                'strsingle' => 
                array (
                    'name' => 'strsingle',
                    'case' => true,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/\'/',
                    'end' => '/\'/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 4,
                    'lookfor' => 
                    array (
                        0 => 'entity',
                    ),
                ),
                'strdouble' => 
                array (
                    'name' => 'strdouble',
                    'case' => true,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/"/',
                    'end' => '/"/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 5,
                    'lookfor' => 
                    array (
                        0 => 'entity',
                    ),
                ),
                'tagname' => 
                array (
                    'name' => 'tagname',
                    'case' => true,
                    'innerGroup' => 'var',
                    'match' => '/^!(ENTITY|ATTLIST|ELEMENT|NOTATION)\\b/',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 6,
                ),
                'reserved' => 
                array (
                    'name' => 'reserved',
                    'case' => true,
                    'innerGroup' => 'reserved',
                    'match' => '/\\s(#(IMPLIED|REQUIRED|FIXED))|CDATA|ENTITY|NOTATION|NMTOKENS?|PUBLIC|SYSTEM\\b/',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 7,
                ),
                'pcdata' => 
                array (
                    'name' => 'pcdata',
                    'case' => true,
                    'innerGroup' => 'reserved',
                    'match' => '/#PCDATA\\b/',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 8,
                ),
                'entity' => 
                array (
                    'name' => 'entity',
                    'case' => true,
                    'innerGroup' => 'special',
                    'match' => '/(\\&|\\%)[\\w\\-\\.]+;/',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 9,
                ),
                'identifier' => 
                array (
                    'name' => 'identifier',
                    'case' => false,
                    'innerGroup' => 'identifier',
                    'match' => '/[a-z][a-z\\d\\-\\,:]+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 10,
                ),
            ),
            'toplevel' => 
            array (
                0 => 'comment',
                1 => 'redecl',
                2 => 'tag',
                3 => 'entity',
            ),
            'case' => true,
            'defClass' => 'code',
        );

        $this->_options = $options;
    }
}

?>
