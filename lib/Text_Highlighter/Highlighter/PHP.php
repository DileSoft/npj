<?php
/**
 * Auto-generated class. PHP syntax highlighting 
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
 * Auto-generated class. PHP syntax highlighting
 *
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_PHP extends Text_Highlighter
{    /**
     * PHP4 Compatible Constructor
     *
     * @param array  $options
     * @access public
     */
    function Text_Highlighter_PHP($options=array())
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
                'constants' => 
                array (
                    'name' => 'constants',
                    'innerGroup' => 'reserved',
                    'case' => true,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'DIRECTORY_SEPARATOR' => true,
                        'PATH_SEPARATOR' => true,
                    ),
                ),
                'reserved' => 
                array (
                    'name' => 'reserved',
                    'innerGroup' => 'reserved',
                    'case' => false,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'echo' => true,
                        'foreach' => true,
                        'else' => true,
                        'if' => true,
                        'elseif' => true,
                        'for' => true,
                        'as' => true,
                        'while' => true,
                        'break' => true,
                        'continue' => true,
                        'class' => true,
                        'const' => true,
                        'declare' => true,
                        'switch' => true,
                        'case' => true,
                        'endfor' => true,
                        'endswitch' => true,
                        'endforeach' => true,
                        'endif' => true,
                        'array' => true,
                        'default' => true,
                        'do' => true,
                        'enddeclare' => true,
                        'eval' => true,
                        'exit' => true,
                        'die' => true,
                        'extends' => true,
                        'function' => true,
                        'global' => true,
                        'include' => true,
                        'include_once' => true,
                        'require' => true,
                        'require_once' => true,
                        'isset' => true,
                        'empty' => true,
                        'list' => true,
                        'new' => true,
                        'static' => true,
                        'unset' => true,
                        'var' => true,
                        'return' => true,
                        'try' => true,
                        'catch' => true,
                        'final' => true,
                        'throw' => true,
                        'public' => true,
                        'private' => true,
                        'protected' => true,
                        'abstract' => true,
                        'interface' => true,
                        'implements' => true,
                        'define' => true,
                        '__file__' => true,
                        '__line__' => true,
                        '__class__' => true,
                        '__method__' => true,
                        '__function__' => true,
                        'null' => true,
                        'true' => true,
                        'false' => true,
                        'and' => true,
                        'or' => true,
                        'xor' => true,
                    ),
                ),
            ),
            'blocks' => 
            array (
                'phpCode' => 
                array (
                    'name' => 'phpCode',
                    'case' => false,
                    'innerGroup' => 'code',
                    'delimGroup' => 'inlinetags',
                    'start' => '/\\<\\?(php|=)?/i',
                    'end' => '/\\?\\>/i',
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
                        5 => 'exec',
                        6 => 'heredoc',
                        7 => 'strsingle',
                        8 => 'comment',
                        9 => 'identifier',
                        10 => 'typecast',
                        11 => 'hexinteger',
                        12 => 'var',
                        13 => 'integer',
                        14 => 'octinteger',
                        15 => 'float',
                        16 => 'exponent',
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
                    'order' => 1,
                    'lookfor' => 
                    array (
                        0 => 'block',
                        1 => 'brackets',
                        2 => 'sqbrackets',
                        3 => 'mlcomment',
                        4 => 'strdouble',
                        5 => 'exec',
                        6 => 'heredoc',
                        7 => 'strsingle',
                        8 => 'comment',
                        9 => 'identifier',
                        10 => 'typecast',
                        11 => 'codeescape',
                        12 => 'hexinteger',
                        13 => 'var',
                        14 => 'integer',
                        15 => 'octinteger',
                        16 => 'float',
                        17 => 'exponent',
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
                    'order' => 2,
                    'lookfor' => 
                    array (
                        0 => 'block',
                        1 => 'brackets',
                        2 => 'sqbrackets',
                        3 => 'mlcomment',
                        4 => 'strdouble',
                        5 => 'exec',
                        6 => 'heredoc',
                        7 => 'strsingle',
                        8 => 'comment',
                        9 => 'identifier',
                        10 => 'typecast',
                        11 => 'hexinteger',
                        12 => 'var',
                        13 => 'integer',
                        14 => 'octinteger',
                        15 => 'float',
                        16 => 'exponent',
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
                    'order' => 3,
                    'lookfor' => 
                    array (
                        0 => 'block',
                        1 => 'brackets',
                        2 => 'sqbrackets',
                        3 => 'mlcomment',
                        4 => 'strdouble',
                        5 => 'exec',
                        6 => 'heredoc',
                        7 => 'strsingle',
                        8 => 'comment',
                        9 => 'identifier',
                        10 => 'typecast',
                        11 => 'hexinteger',
                        12 => 'var',
                        13 => 'integer',
                        14 => 'octinteger',
                        15 => 'float',
                        16 => 'exponent',
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
                    'order' => 4,
                    'lookfor' => 
                    array (
                        0 => 'phpdoc',
                        1 => 'url',
                        2 => 'email',
                        3 => 'note',
                        4 => 'cvstag',
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
                    'order' => 5,
                    'lookfor' => 
                    array (
                        0 => 'descaped',
                        1 => 'curlyvar',
                        2 => 'var',
                    ),
                ),
                'exec' => 
                array (
                    'name' => 'exec',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/`/i',
                    'end' => '/`/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 6,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
                        1 => 'curlyvar',
                        2 => 'var',
                    ),
                ),
                'heredoc' => 
                array (
                    'name' => 'heredoc',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/\\<\\<\\<[\\x20\\x09]*(\\w+)$/mi',
                    'end' => '/^%1%;?$/mi',
                    'remember' => true,
                    'type' => 'region',
                    'order' => 7,
                    'lookfor' => 
                    array (
                        0 => 'descaped',
                        1 => 'curlyvar',
                        2 => 'var',
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
                    'match' => '/\\\\[\\\\"\'`tnr\\$\\{]/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 10,
                ),
                'comment' => 
                array (
                    'name' => 'comment',
                    'case' => false,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'comment',
                    'start' => '/(#|\\/\\/)/i',
                    'end' => '/$|(?=\\?\\>)/mi',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 11,
                    'lookfor' => 
                    array (
                        0 => 'phpdoc',
                        1 => 'url',
                        2 => 'email',
                        3 => 'note',
                        4 => 'cvstag',
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
                    'order' => 12,
                ),
                'typecast' => 
                array (
                    'name' => 'typecast',
                    'case' => false,
                    'innerGroup' => 'reserved',
                    'match' => '/\\((array|int|integer|string|bool|boolean|object|float|double)\\)/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 13,
                ),
                'curlyvar' => 
                array (
                    'name' => 'curlyvar',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/\\{\\$[a-z_].*\\}/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 14,
                ),
                'codeescape' => 
                array (
                    'name' => 'codeescape',
                    'case' => false,
                    'innerGroup' => 'default',
                    'delimGroup' => 'inlinetags',
                    'start' => '/\\?\\>/i',
                    'end' => '/\\<\\?(php|=)?/i',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 15,
                ),
                'hexinteger' => 
                array (
                    'name' => 'hexinteger',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/0[xX][\\da-f]+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 16,
                ),
                'var' => 
                array (
                    'name' => 'var',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/\\$[a-z_]\\w*/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 17,
                ),
                'integer' => 
                array (
                    'name' => 'integer',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/\\d\\d*|\\b0\\b/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 18,
                ),
                'octinteger' => 
                array (
                    'name' => 'octinteger',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/0[0-7]+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 19,
                ),
                'float' => 
                array (
                    'name' => 'float',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/(\\d*\\.\\d+)|(\\d+\\.\\d*)/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 20,
                ),
                'exponent' => 
                array (
                    'name' => 'exponent',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/((\\d+|((\\d*\\.\\d+)|(\\d+\\.\\d*)))[eE][+-]?\\d+)/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 21,
                ),
                'phpdoc' => 
                array (
                    'name' => 'phpdoc',
                    'case' => false,
                    'innerGroup' => 'inlinedoc',
                    'match' => '/\\s@\\w+\\s/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 22,
                ),
                'url' => 
                array (
                    'name' => 'url',
                    'case' => false,
                    'innerGroup' => 'url',
                    'match' => '/((https?|ftp):\\/\\/[\\w\\?\\.\\-\\&=\\/]+([^\\w\\?\\.\\&=\\/]|$))|(^|[\\s,!?])www\\.\\w+\\.\\w+[\\w\\?\\.\\&=\\/]*([^\\w\\?\\.\\&=\\/]|$)/mi',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 23,
                ),
                'email' => 
                array (
                    'name' => 'email',
                    'case' => false,
                    'innerGroup' => 'url',
                    'match' => '/\\w+[\\.\\w\\-]+@(\\w+[\\.\\w\\-])+/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 24,
                ),
                'note' => 
                array (
                    'name' => 'note',
                    'case' => false,
                    'innerGroup' => 'inlinedoc',
                    'match' => '/\\bnote:/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 25,
                ),
                'cvstag' => 
                array (
                    'name' => 'cvstag',
                    'case' => false,
                    'innerGroup' => 'inlinedoc',
                    'match' => '/\\$\\w+\\s*:.*\\$/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 26,
                ),
            ),
            'toplevel' => 
            array (
                0 => 'phpCode',
            ),
            'case' => false,
            'defClass' => 'code',
        );

        $this->_options = $options;
    }
}

?>
