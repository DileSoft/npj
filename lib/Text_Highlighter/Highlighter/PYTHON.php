<?php
/**
 * Auto-generated class. PYTHON syntax highlighting 
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
 * Auto-generated class. PYTHON syntax highlighting
 *
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_PYTHON extends Text_Highlighter
{    /**
     * PHP4 Compatible Constructor
     *
     * @param array  $options
     * @access public
     */
    function Text_Highlighter_PYTHON($options=array())
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
                        'del' => true,
                        'for' => true,
                        'is' => true,
                        'raise' => true,
                        'assert' => true,
                        'elif' => true,
                        'from' => true,
                        'lambda' => true,
                        'return' => true,
                        'break' => true,
                        'else' => true,
                        'global' => true,
                        'not' => true,
                        'try' => true,
                        'class' => true,
                        'except' => true,
                        'if' => true,
                        'or' => true,
                        'while' => true,
                        'continue' => true,
                        'exec' => true,
                        'import' => true,
                        'pass' => true,
                        'yield' => true,
                        'def' => true,
                        'finally' => true,
                        'in' => true,
                        'print' => true,
                        'False' => true,
                        'True' => true,
                        'None' => true,
                        'NotImplemented' => true,
                        'Ellipsis' => true,
                        'Exception' => true,
                        'SystemExit' => true,
                        'StopIteration' => true,
                        'StandardError' => true,
                        'KeyboardInterrupt' => true,
                        'ImportError' => true,
                        'EnvironmentError' => true,
                        'IOError' => true,
                        'OSError' => true,
                        'WindowsError' => true,
                        'EOFError' => true,
                        'RuntimeError' => true,
                        'NotImplementedError' => true,
                        'NameError' => true,
                        'UnboundLocalError' => true,
                        'AttributeError' => true,
                        'SyntaxError' => true,
                        'IndentationError' => true,
                        'TabError' => true,
                        'TypeError' => true,
                        'AssertionError' => true,
                        'LookupError' => true,
                        'IndexError' => true,
                        'KeyError' => true,
                        'ArithmeticError' => true,
                        'OverflowError' => true,
                        'ZeroDivisionError' => true,
                        'FloatingPointError' => true,
                        'ValueError' => true,
                        'UnicodeError' => true,
                        'UnicodeEncodeError' => true,
                        'UnicodeDecodeError' => true,
                        'UnicodeTranslateError' => true,
                        'ReferenceError' => true,
                        'SystemError' => true,
                        'MemoryError' => true,
                        'Warning' => true,
                        'UserWarning' => true,
                        'DeprecationWarning' => true,
                        'PendingDeprecationWarning' => true,
                        'SyntaxWarning' => true,
                        'OverflowWarning' => true,
                        'RuntimeWarning' => true,
                        'FutureWarning' => true,
                    ),
                ),
                'builtin' => 
                array (
                    'name' => 'builtin',
                    'innerGroup' => 'builtin',
                    'case' => true,
                    'inherits' => 'possiblefunction',
                    'otherwise' => 'identifier',
                    'match' => 
                    array (
                        '__import__' => true,
                        'abs' => true,
                        'apply' => true,
                        'basestring' => true,
                        'bool' => true,
                        'buffer' => true,
                        'callable' => true,
                        'chr' => true,
                        'classmethod' => true,
                        'cmp' => true,
                        'coerce' => true,
                        'compile' => true,
                        'complex' => true,
                        'delattr' => true,
                        'dict' => true,
                        'dir' => true,
                        'divmod' => true,
                        'enumerate' => true,
                        'eval' => true,
                        'execfile' => true,
                        'file' => true,
                        'filter' => true,
                        'float' => true,
                        'getattr' => true,
                        'globals' => true,
                        'hasattr' => true,
                        'hash' => true,
                        'help' => true,
                        'hex' => true,
                        'id' => true,
                        'input' => true,
                        'int' => true,
                        'intern' => true,
                        'isinstance' => true,
                        'issubclass' => true,
                        'iter' => true,
                        'len' => true,
                        'list' => true,
                        'locals' => true,
                        'long' => true,
                        'map' => true,
                        'max' => true,
                        'min' => true,
                        'object' => true,
                        'oct' => true,
                        'open' => true,
                        'ord' => true,
                        'pow' => true,
                        'property' => true,
                        'range' => true,
                        'raw_input' => true,
                        'reduce' => true,
                        'reload' => true,
                        'repr' => true,
                        'round' => true,
                        'setattr' => true,
                        'slice' => true,
                        'staticmethod' => true,
                        'sum' => true,
                        'super' => true,
                        'str' => true,
                        'tuple' => true,
                        'type' => true,
                        'unichr' => true,
                        'unicode' => true,
                        'vars' => true,
                        'xrange' => true,
                        'zip' => true,
                    ),
                ),
            ),
            'blocks' => 
            array (
                'strsingle3' => 
                array (
                    'name' => 'strsingle3',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/\'\'\'/i',
                    'end' => '/\'\'\'/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 0,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
                    ),
                ),
                'strdouble3' => 
                array (
                    'name' => 'strdouble3',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/"""/i',
                    'end' => '/"""/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 1,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
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
                    'order' => 2,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
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
                    'order' => 3,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
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
                    'order' => 4,
                    'lookfor' => 
                    array (
                        0 => 'strsingle3',
                        1 => 'strdouble3',
                        2 => 'strdouble',
                        3 => 'strsingle',
                        4 => 'brackets',
                        5 => 'sqbrackets',
                        6 => 'possiblefunction',
                        7 => 'identifier',
                        8 => 'exponent',
                        9 => 'imaginary',
                        10 => 'float',
                        11 => 'integer',
                        12 => 'hexinteger',
                        13 => 'octinteger',
                        14 => 'comment',
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
                    'order' => 5,
                    'lookfor' => 
                    array (
                        0 => 'strsingle3',
                        1 => 'strdouble3',
                        2 => 'strdouble',
                        3 => 'strsingle',
                        4 => 'brackets',
                        5 => 'sqbrackets',
                        6 => 'possiblefunction',
                        7 => 'identifier',
                        8 => 'exponent',
                        9 => 'imaginary',
                        10 => 'float',
                        11 => 'integer',
                        12 => 'hexinteger',
                        13 => 'octinteger',
                        14 => 'comment',
                    ),
                ),
                'escaped' => 
                array (
                    'name' => 'escaped',
                    'case' => false,
                    'innerGroup' => 'special',
                    'match' => '/\\\\./i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 6,
                ),
                'possiblefunction' => 
                array (
                    'name' => 'possiblefunction',
                    'case' => false,
                    'innerGroup' => 'identifier',
                    'match' => '/[a-z_]\\w*(?=\\s*\\()/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 7,
                ),
                'identifier' => 
                array (
                    'name' => 'identifier',
                    'case' => false,
                    'innerGroup' => 'identifier',
                    'match' => '/[a-z_]\\w*/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 8,
                ),
                'exponent' => 
                array (
                    'name' => 'exponent',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/((\\d+|((\\d*\\.\\d+)|(\\d+\\.\\d*)))[eE][+-]?\\d+)/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 9,
                ),
                'imaginary' => 
                array (
                    'name' => 'imaginary',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/((\\d*\\.\\d+)|(\\d+\\.\\d*)|(\\d+))j/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 10,
                ),
                'float' => 
                array (
                    'name' => 'float',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/(\\d*\\.\\d+)|(\\d+\\.\\d*)/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 11,
                ),
                'integer' => 
                array (
                    'name' => 'integer',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/\\d+l?|\\b0l?\\b/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 12,
                ),
                'hexinteger' => 
                array (
                    'name' => 'hexinteger',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/0[xX][\\da-f]+l?/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 13,
                ),
                'octinteger' => 
                array (
                    'name' => 'octinteger',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/0[0-7]+l?/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 14,
                ),
                'comment' => 
                array (
                    'name' => 'comment',
                    'case' => false,
                    'innerGroup' => 'comment',
                    'match' => '/#.+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 15,
                ),
            ),
            'toplevel' => 
            array (
                0 => 'strsingle3',
                1 => 'strdouble3',
                2 => 'strdouble',
                3 => 'strsingle',
                4 => 'brackets',
                5 => 'sqbrackets',
                6 => 'possiblefunction',
                7 => 'identifier',
                8 => 'exponent',
                9 => 'imaginary',
                10 => 'float',
                11 => 'integer',
                12 => 'hexinteger',
                13 => 'octinteger',
                14 => 'comment',
            ),
            'case' => false,
            'defClass' => 'code',
        );

        $this->_options = $options;
    }
}

?>
