<?php
/**
 * Auto-generated class. JAVASCRIPT syntax highlighting 
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
 * Auto-generated class. JAVASCRIPT syntax highlighting
 *
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_JAVASCRIPT extends Text_Highlighter
{    /**
     * PHP4 Compatible Constructor
     *
     * @param array  $options
     * @access public
     */
    function Text_Highlighter_JAVASCRIPT($options=array())
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
                'builtin' => 
                array (
                    'name' => 'builtin',
                    'innerGroup' => 'builtin',
                    'case' => true,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'String' => true,
                        'Array' => true,
                        'RegExp' => true,
                        'Function' => true,
                        'Math' => true,
                        'Number' => true,
                        'Date' => true,
                        'Image' => true,
                        'window' => true,
                        'document' => true,
                        'navigator' => true,
                        'onAbort' => true,
                        'onBlur' => true,
                        'onChange' => true,
                        'onClick' => true,
                        'onDblClick' => true,
                        'onDragDrop' => true,
                        'onError' => true,
                        'onFocus' => true,
                        'onKeyDown' => true,
                        'onKeyPress' => true,
                        'onKeyUp' => true,
                        'onLoad' => true,
                        'onMouseDown' => true,
                        'onMouseOver' => true,
                        'onMouseOut' => true,
                        'onMouseMove' => true,
                        'onMouseUp' => true,
                        'onMove' => true,
                        'onReset' => true,
                        'onResize' => true,
                        'onSelect' => true,
                        'onSubmit' => true,
                        'onUnload' => true,
                    ),
                ),
                'reserved' => 
                array (
                    'name' => 'reserved',
                    'innerGroup' => 'reserved',
                    'case' => true,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'break' => true,
                        'continue' => true,
                        'do' => true,
                        'while' => true,
                        'export' => true,
                        'for' => true,
                        'in' => true,
                        'if' => true,
                        'else' => true,
                        'import' => true,
                        'return' => true,
                        'label' => true,
                        'switch' => true,
                        'case' => true,
                        'var' => true,
                        'with' => true,
                        'delete' => true,
                        'new' => true,
                        'this' => true,
                        'typeof' => true,
                        'void' => true,
                        'abstract' => true,
                        'boolean' => true,
                        'byte' => true,
                        'catch' => true,
                        'char' => true,
                        'class' => true,
                        'const' => true,
                        'debugger' => true,
                        'default' => true,
                        'double' => true,
                        'enum' => true,
                        'extends' => true,
                        'false' => true,
                        'final' => true,
                        'finally' => true,
                        'float' => true,
                        'function' => true,
                        'implements' => true,
                        'goto' => true,
                        'instanceof' => true,
                        'int' => true,
                        'interface' => true,
                        'long' => true,
                        'native' => true,
                        'null' => true,
                        'package' => true,
                        'private' => true,
                        'protected' => true,
                        'public' => true,
                        'short' => true,
                        'static' => true,
                        'super' => true,
                        'synchronized' => true,
                        'throw' => true,
                        'throws' => true,
                        'transient' => true,
                        'true' => true,
                        'try' => true,
                        'volatile' => true,
                    ),
                ),
            ),
            'blocks' => 
            array (
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
                    'order' => 0,
                    'lookfor' => 
                    array (
                        0 => 'block',
                        1 => 'brackets',
                        2 => 'sqbrackets',
                        3 => 'mlcomment',
                        4 => 'strdouble',
                        5 => 'strsingle',
                        6 => 'comment',
                        7 => 'regexp',
                        8 => 'identifier',
                        9 => 'number',
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
                    'order' => 1,
                    'lookfor' => 
                    array (
                        0 => 'block',
                        1 => 'brackets',
                        2 => 'sqbrackets',
                        3 => 'mlcomment',
                        4 => 'strdouble',
                        5 => 'strsingle',
                        6 => 'comment',
                        7 => 'regexp',
                        8 => 'identifier',
                        9 => 'number',
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
                    'order' => 2,
                    'lookfor' => 
                    array (
                        0 => 'block',
                        1 => 'brackets',
                        2 => 'sqbrackets',
                        3 => 'mlcomment',
                        4 => 'strdouble',
                        5 => 'strsingle',
                        6 => 'comment',
                        7 => 'regexp',
                        8 => 'identifier',
                        9 => 'number',
                    ),
                ),
                'mlcomment' => 
                array (
                    'name' => 'mlcomment',
                    'case' => false,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'comment',
                    'start' => '/\\/\\*/i',
                    'end' => '/\\*\\//i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 3,
                    'lookfor' => 
                    array (
                        0 => 'url',
                        1 => 'email',
                        2 => 'note',
                        3 => 'cvstag',
                    ),
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
                    'order' => 4,
                    'lookfor' => 
                    array (
                        0 => 'descaped',
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
                    'order' => 5,
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
                    'order' => 6,
                ),
                'descaped' => 
                array (
                    'name' => 'descaped',
                    'case' => false,
                    'innerGroup' => 'special',
                    'match' => '/\\\\\\\\|\\\\"|\\\\\'|\\\\`|\\\\t|\\\\n|\\\\r/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 7,
                ),
                'reescaped' => 
                array (
                    'name' => 'reescaped',
                    'case' => false,
                    'innerGroup' => 'special',
                    'match' => '/\\\\\\//i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 8,
                ),
                'comment' => 
                array (
                    'name' => 'comment',
                    'case' => false,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'comment',
                    'start' => '/\\/\\//i',
                    'end' => '/$/mi',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 9,
                    'lookfor' => 
                    array (
                        0 => 'url',
                        1 => 'email',
                        2 => 'note',
                        3 => 'cvstag',
                    ),
                ),
                'regexp' => 
                array (
                    'name' => 'regexp',
                    'case' => true,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/\\//',
                    'end' => '/\\/g?i?/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 10,
                    'lookfor' => 
                    array (
                        0 => 'reescaped',
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
                    'order' => 11,
                ),
                'number' => 
                array (
                    'name' => 'number',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/\\d*\\.?\\d+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 12,
                ),
                'url' => 
                array (
                    'name' => 'url',
                    'case' => false,
                    'innerGroup' => 'url',
                    'match' => '/((https?|ftp):\\/\\/[\\w\\?\\.\\-\\&=\\/]+([^\\w\\?\\.\\&=\\/]|$))|(^|[\\s,!?])www\\.\\w+\\.\\w+[\\w\\?\\.\\&=\\/]*([^\\w\\?\\.\\&=\\/]|$)/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 13,
                ),
                'email' => 
                array (
                    'name' => 'email',
                    'case' => false,
                    'innerGroup' => 'url',
                    'match' => '/\\w+[\\.\\w\\-]+@(\\w+[\\.\\w\\-])+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 14,
                ),
                'note' => 
                array (
                    'name' => 'note',
                    'case' => false,
                    'innerGroup' => 'inlinedoc',
                    'match' => '/\\b(note|fixme):/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 15,
                ),
                'cvstag' => 
                array (
                    'name' => 'cvstag',
                    'case' => false,
                    'innerGroup' => 'inlinedoc',
                    'match' => '/\\$\\w+:.+\\$/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 16,
                ),
            ),
            'toplevel' => 
            array (
                0 => 'block',
                1 => 'brackets',
                2 => 'sqbrackets',
                3 => 'mlcomment',
                4 => 'strdouble',
                5 => 'strsingle',
                6 => 'comment',
                7 => 'regexp',
                8 => 'identifier',
                9 => 'number',
            ),
            'case' => false,
            'defClass' => 'code',
        );

        $this->_options = $options;
    }
}

?>
