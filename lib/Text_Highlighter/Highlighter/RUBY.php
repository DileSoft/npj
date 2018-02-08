<?php
/**
 * Auto-generated class. RUBY syntax highlighting
 * 
 * 
 * FIXME:  While this construction : s.split /z/i 
 * is valid, regular expression is not recognized as such
 * (/ folowing an identifier or number is not recognized as
 * start of RE), making highlighting improper
 * 
 * %q(a (nested) string) does not get highlighted correctly
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
 * @version    CVS: $Id: RUBY.php,v 1.3 2004/10/07 14:28:15 blindman Exp $
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 *
 */

/**
 * @ignore
 */

require_once 'Text/Highlighter.php';

/**
 * Auto-generated class. RUBY syntax highlighting
 *
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_RUBY extends Text_Highlighter
{    /**
     * PHP4 Compatible Constructor
     *
     * @param array  $options
     * @access public
     */
    function Text_Highlighter_RUBY($options=array())
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
                        '__FILE__' => true,
                        'require' => true,
                        'and' => true,
                        'def' => true,
                        'end' => true,
                        'in' => true,
                        'or' => true,
                        'self' => true,
                        'unless' => true,
                        '__LINE__' => true,
                        'begin' => true,
                        'defined?' => true,
                        'ensure' => true,
                        'module' => true,
                        'redo' => true,
                        'super' => true,
                        'until' => true,
                        'BEGIN' => true,
                        'break' => true,
                        'do' => true,
                        'false' => true,
                        'next' => true,
                        'rescue' => true,
                        'then' => true,
                        'when' => true,
                        'END' => true,
                        'case' => true,
                        'else' => true,
                        'for' => true,
                        'nil' => true,
                        'retry' => true,
                        'true' => true,
                        'while' => true,
                        'alias' => true,
                        'module_function' => true,
                        'private' => true,
                        'public' => true,
                        'protected' => true,
                        'attr_reader' => true,
                        'attr_writer' => true,
                        'attr_accessor' => true,
                        'class' => true,
                        'elsif' => true,
                        'if' => true,
                        'not' => true,
                        'return' => true,
                        'undef' => true,
                        'yield' => true,
                    ),
                ),
            ),
            'blocks' => 
            array (
                'data' => 
                array (
                    'name' => 'data',
                    'case' => false,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'reserved',
                    'start' => '/^__END__$/mi',
                    'end' => '/$/i',
                    'remember' => false,
                    'type' => 'region',
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
                'qstrdouble' => 
                array (
                    'name' => 'qstrdouble',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/%[Qx]([!"#\\$%&\'+\\-*.\\/:;=?@^`|~{<\\[(])/i',
                    'end' => '/%b1%/i',
                    'remember' => true,
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
                'qstrsingle' => 
                array (
                    'name' => 'qstrsingle',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/%[wq]([!"#\\$%&\'+\\-*.\\/:;=?@^`|~{<\\[(])/i',
                    'end' => '/%b1%/i',
                    'remember' => true,
                    'type' => 'region',
                    'order' => 4,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
                    ),
                ),
                'global' => 
                array (
                    'name' => 'global',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/\\$(\\W|\\w+)/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 5,
                ),
                'classvar' => 
                array (
                    'name' => 'classvar',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/@@?[_a-z][\\d_a-z]*/ii',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 6,
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
                    'order' => 7,
                    'lookfor' => 
                    array (
                        0 => 'data',
                        1 => 'strdouble',
                        2 => 'qstrdouble',
                        3 => 'strsingle',
                        4 => 'qstrsingle',
                        5 => 'global',
                        6 => 'classvar',
                        7 => 'brackets',
                        8 => 'sqbrackets',
                        9 => 'identifier',
                        10 => 'exponent',
                        11 => 'float',
                        12 => 'hexinteger',
                        13 => 'integer',
                        14 => 'octinteger',
                        15 => 'rubydoc',
                        16 => 'comment',
                        17 => 'regexp',
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
                    'order' => 8,
                    'lookfor' => 
                    array (
                        0 => 'data',
                        1 => 'strdouble',
                        2 => 'qstrdouble',
                        3 => 'strsingle',
                        4 => 'qstrsingle',
                        5 => 'global',
                        6 => 'classvar',
                        7 => 'brackets',
                        8 => 'sqbrackets',
                        9 => 'identifier',
                        10 => 'exponent',
                        11 => 'float',
                        12 => 'hexinteger',
                        13 => 'integer',
                        14 => 'octinteger',
                        15 => 'rubydoc',
                        16 => 'comment',
                        17 => 'regexp',
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
                    'order' => 9,
                ),
                'identifier' => 
                array (
                    'name' => 'identifier',
                    'case' => false,
                    'innerGroup' => 'identifier',
                    'match' => '/[a-z_]\\w*/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 10,
                ),
                'exponent' => 
                array (
                    'name' => 'exponent',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/((\\d+|((\\d*\\.\\d+)|(\\d+\\.\\d*)))[eE][+-]?\\d+)/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 11,
                ),
                'float' => 
                array (
                    'name' => 'float',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/(\\d*\\.\\d+)|(\\d+\\.\\d*)/i',
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
                'integer' => 
                array (
                    'name' => 'integer',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/\\d+l?|\\b0l?\\b/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 14,
                ),
                'octinteger' => 
                array (
                    'name' => 'octinteger',
                    'case' => false,
                    'innerGroup' => 'number',
                    'match' => '/0[0-7]+l?/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 15,
                ),
                'rubydoc' => 
                array (
                    'name' => 'rubydoc',
                    'case' => false,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'comment',
                    'start' => '/^=begin$/mi',
                    'end' => '/^=end$/mi',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 16,
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
                    'order' => 17,
                ),
                'comment' => 
                array (
                    'name' => 'comment',
                    'case' => false,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'comment',
                    'start' => '/#/i',
                    'end' => '/$/mi',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 18,
                    'lookfor' => 
                    array (
                        0 => 'cvstag',
                    ),
                ),
                'regexp' => 
                array (
                    'name' => 'regexp',
                    'case' => false,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/\\s*\\//i',
                    'end' => '/\\/[iomx]*/i',
                    'remember' => false,
                    'neverafter' => '/(?<!\\band|\\bor|\\bwhile|\\buntil|\\bunless|\\bif|\\belsif|\\bwhen|[~=!|&(,\\[])$/i',
                    'type' => 'region',
                    'order' => 19,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
                    ),
                ),
            ),
            'toplevel' => 
            array (
                0 => 'data',
                1 => 'strdouble',
                2 => 'qstrdouble',
                3 => 'strsingle',
                4 => 'qstrsingle',
                5 => 'global',
                6 => 'classvar',
                7 => 'brackets',
                8 => 'sqbrackets',
                9 => 'identifier',
                10 => 'exponent',
                11 => 'float',
                12 => 'hexinteger',
                13 => 'integer',
                14 => 'octinteger',
                15 => 'rubydoc',
                16 => 'comment',
                17 => 'regexp',
            ),
            'case' => false,
            'defClass' => 'code',
        );

        $this->_options = $options;
    }
}

?>
