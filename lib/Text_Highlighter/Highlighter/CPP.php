<?php
/**
 * Auto-generated class. CPP syntax highlighting
 * 
 * 
 * Thanks to Aaron Kalin for initial
 * implementation of this highlighter
 *      
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
 * @author Aaron Kalin
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 *
 */

/**
 * @ignore
 */


/**
 * Auto-generated class. CPP syntax highlighting
 *
 * @author Aaron Kalin
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_CPP extends Text_Highlighter
{    /**
     * PHP4 Compatible Constructor
     *
     * @param array  $options
     * @access public
     */
    function Text_Highlighter_CPP($options=array())
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
                'reserved' => 
                array (
                    'name' => 'reserved',
                    'innerGroup' => 'reserved',
                    'case' => true,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'and' => true,
                        'and_eq' => true,
                        'asm' => true,
                        'bitand' => true,
                        'bitor' => true,
                        'break' => true,
                        'case' => true,
                        'catch' => true,
                        'compl' => true,
                        'const_cast' => true,
                        'continue' => true,
                        'default' => true,
                        'delete' => true,
                        'do' => true,
                        'dynamic_cast' => true,
                        'else' => true,
                        'for' => true,
                        'fortran' => true,
                        'friend' => true,
                        'goto' => true,
                        'if' => true,
                        'new' => true,
                        'not' => true,
                        'not_eq' => true,
                        'operator' => true,
                        'or' => true,
                        'or_eq' => true,
                        'private' => true,
                        'protected' => true,
                        'public' => true,
                        'reinterpret_cast' => true,
                        'return' => true,
                        'sizeof' => true,
                        'static_cast' => true,
                        'switch' => true,
                        'this' => true,
                        'throw' => true,
                        'try' => true,
                        'typeid' => true,
                        'using' => true,
                        'while' => true,
                        'xor' => true,
                        'xor_eq' => true,
                    ),
                ),
                'types' => 
                array (
                    'name' => 'types',
                    'innerGroup' => 'types',
                    'case' => true,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'auto' => true,
                        'bool' => true,
                        'char' => true,
                        'class' => true,
                        'const' => true,
                        'double' => true,
                        'enum' => true,
                        'explicit' => true,
                        'export' => true,
                        'extern' => true,
                        'float' => true,
                        'inline' => true,
                        'int' => true,
                        'long' => true,
                        'mutable' => true,
                        'namespace' => true,
                        'register' => true,
                        'short' => true,
                        'signed' => true,
                        'static' => true,
                        'struct' => true,
                        'template' => true,
                        'typedef' => true,
                        'typename' => true,
                        'union' => true,
                        'unsigned' => true,
                        'virtual' => true,
                        'void' => true,
                        'volatile' => true,
                        'wchar_t' => true,
                    ),
                ),
                'Common Macros' => 
                array (
                    'name' => 'Common Macros',
                    'innerGroup' => 'prepro',
                    'case' => true,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'NULL' => true,
                        'TRUE' => true,
                        'FALSE' => true,
                        'MAX' => true,
                        'MIN' => true,
                        '__LINE__' => true,
                        '__DATA__' => true,
                        '__FILE__' => true,
                        '__TIME__' => true,
                        '__STDC__' => true,
                        'false' => true,
                        'true' => true,
                    ),
                ),
            ),
            'blocks' => 
            array (
                'escaped' => 
                array (
                    'name' => 'escaped',
                    'case' => false,
                    'innerGroup' => 'special',
                    'match' => '/\\\\/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 0,
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
                    'order' => 1,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
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
                    'order' => 2,
                    'lookfor' => 
                    array (
                        0 => 'strdouble',
                        1 => 'block',
                        2 => 'brackets',
                        3 => 'sqbrackets',
                        4 => 'identifier',
                        5 => 'hexinteger',
                        6 => 'integer',
                        7 => 'octinteger',
                        8 => 'float',
                        9 => 'include',
                        10 => 'preprocessor',
                        11 => 'number',
                        12 => 'mlcomment',
                        13 => 'comment',
                    ),
                ),
                'brackets' => 
                array (
                    'name' => 'brackets',
                    'case' => false,
                    'innerGroup' => 'code',
                    'delimGroup' => 'brackets',
                    'start' => '/\\(/i',
                    'end' => '/\\)/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 3,
                    'lookfor' => 
                    array (
                        0 => 'strdouble',
                        1 => 'block',
                        2 => 'brackets',
                        3 => 'sqbrackets',
                        4 => 'identifier',
                        5 => 'hexinteger',
                        6 => 'integer',
                        7 => 'octinteger',
                        8 => 'float',
                        9 => 'include',
                        10 => 'preprocessor',
                        11 => 'number',
                        12 => 'mlcomment',
                        13 => 'comment',
                    ),
                ),
                'sqbrackets' => 
                array (
                    'name' => 'sqbrackets',
                    'case' => false,
                    'innerGroup' => 'code',
                    'delimGroup' => 'brackets',
                    'start' => '/\\[/i',
                    'end' => '/\\]/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 4,
                    'lookfor' => 
                    array (
                        0 => 'strdouble',
                        1 => 'block',
                        2 => 'brackets',
                        3 => 'sqbrackets',
                        4 => 'identifier',
                        5 => 'hexinteger',
                        6 => 'integer',
                        7 => 'octinteger',
                        8 => 'float',
                        9 => 'include',
                        10 => 'preprocessor',
                        11 => 'number',
                        12 => 'mlcomment',
                        13 => 'comment',
                    ),
                ),
                'identifier' => 
                array (
                    'name' => 'identifier',
                    'case' => false,
                    'innerGroup' => 'identifier',
                    'match' => '/[a-z_]\\w*/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 5,
                ),
                'hexinteger' => 
                array (
                    'name' => 'hexinteger',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/\\b0[xX][\\da-f]+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 6,
                ),
                'integer' => 
                array (
                    'name' => 'integer',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/\\b\\d\\d*|\\b0\\b/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 7,
                ),
                'octinteger' => 
                array (
                    'name' => 'octinteger',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/\\b0[0-7]+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 8,
                ),
                'float' => 
                array (
                    'name' => 'float',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/\\b(\\d*\\.\\d+)|(\\d+\\.\\d*)/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 9,
                ),
                'strincl' => 
                array (
                    'name' => 'strincl',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/</i',
                    'end' => '/>/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 10,
                ),
                'include' => 
                array (
                    'name' => 'include',
                    'case' => false,
                    'innerGroup' => 'prepro',
                    'delimGroup' => 'prepro',
                    'start' => '/^[ \\t]*#include/mi',
                    'end' => '/(?<!\\\\)$/mi',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 11,
                    'lookfor' => 
                    array (
                        0 => 'strdouble',
                        1 => 'strincl',
                    ),
                ),
                'preprocessor' => 
                array (
                    'name' => 'preprocessor',
                    'case' => false,
                    'innerGroup' => 'prepro',
                    'delimGroup' => 'prepro',
                    'start' => '/^[ \\t]*#[ \\t]*/mi',
                    'end' => '/(?<!\\\\)$/mi',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 12,
                    'lookfor' => 
                    array (
                        0 => 'strdouble',
                        1 => 'hexinteger',
                        2 => 'integer',
                        3 => 'octinteger',
                        4 => 'float',
                    ),
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
                'mlcomment' => 
                array (
                    'name' => 'mlcomment',
                    'case' => false,
                    'innerGroup' => 'mlcomment',
                    'delimGroup' => 'mlcomment',
                    'start' => '/\\/\\*/i',
                    'end' => '/\\*\\//i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 14,
                    'lookfor' => 
                    array (
                        0 => 'cvstag',
                    ),
                ),
                'cvstag' => 
                array (
                    'name' => 'cvstag',
                    'case' => false,
                    'innerGroup' => 'inlinedoc',
                    'match' => '/\\$\\w+\\s*:.+\\$/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 15,
                ),
                'comment' => 
                array (
                    'name' => 'comment',
                    'case' => false,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'comment',
                    'start' => '/\\/\\/.+/i',
                    'end' => '/$/mi',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 16,
                    'lookfor' => 
                    array (
                        0 => 'cvstag',
                    ),
                ),
            ),
            'toplevel' => 
            array (
                0 => 'strdouble',
                1 => 'block',
                2 => 'brackets',
                3 => 'sqbrackets',
                4 => 'identifier',
                5 => 'strincl',
                6 => 'include',
                7 => 'preprocessor',
                8 => 'number',
                9 => 'mlcomment',
                10 => 'cvstag',
                11 => 'comment',
            ),
            'case' => false,
            'defClass' => 'code',
        );

        $this->_options = $options;
    }
}

?>
