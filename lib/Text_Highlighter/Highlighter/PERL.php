<?php
/**
 * Auto-generated class. PERL syntax highlighting
 * 
 * This highlighter is EXPERIMENTAL, so that it may work incorrectly. 
 * Most rules were created by Mariusz Jakubowski, and extended by me.
 * My  knowledge  of  Perl  is  poor,  and  Perl  syntax  seems  too
 * complicated to me. 
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
 * @version    CVS: $Id: PERL.php,v 1.3 2004/10/07 14:28:15 blindman Exp $
 * @author Mariusz 'kg' Jakubowski <kg@alternatywa.info>
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 *
 */

/**
 * @ignore
 */

require_once 'Text/Highlighter.php';

/**
 * Auto-generated class. PERL syntax highlighting
 *
 * @author Mariusz 'kg' Jakubowski <kg@alternatywa.info>
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_PERL extends Text_Highlighter
{    /**
     * PHP4 Compatible Constructor
     *
     * @param array  $options
     * @access public
     */
    function Text_Highlighter_PERL($options=array())
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
                        'abs' => true,
                        'accept' => true,
                        'alarm' => true,
                        'atan2' => true,
                        'bind' => true,
                        'binmode' => true,
                        'bless' => true,
                        'caller' => true,
                        'chdir' => true,
                        'chmod' => true,
                        'chomp' => true,
                        'chop' => true,
                        'chown' => true,
                        'chr' => true,
                        'chroot' => true,
                        'close' => true,
                        'closedir' => true,
                        'connect' => true,
                        'continue' => true,
                        'cos' => true,
                        'crypt' => true,
                        'dbmclose' => true,
                        'dbmopen' => true,
                        'defined' => true,
                        'delete' => true,
                        'die' => true,
                        'do' => true,
                        'dump' => true,
                        'each' => true,
                        'endgrent' => true,
                        'endhostent' => true,
                        'endnetent' => true,
                        'endprotoent' => true,
                        'endpwent' => true,
                        'endservent' => true,
                        'eof' => true,
                        'eval' => true,
                        'exec' => true,
                        'exists' => true,
                        'exit' => true,
                        'exp' => true,
                        'fcntl' => true,
                        'fileno' => true,
                        'flock' => true,
                        'fork' => true,
                        'format' => true,
                        'formline' => true,
                        'getc' => true,
                        'getgrent' => true,
                        'getgrgid' => true,
                        'getgrnam' => true,
                        'gethostbyaddr' => true,
                        'gethostbyname' => true,
                        'gethostent' => true,
                        'getlogin' => true,
                        'getnetbyaddr' => true,
                        'getnetbyname' => true,
                        'getnetent' => true,
                        'getpeername' => true,
                        'getpgrp' => true,
                        'getppid' => true,
                        'getpriority' => true,
                        'getprotobyname' => true,
                        'getprotobynumber' => true,
                        'getprotoent' => true,
                        'getpwent' => true,
                        'getpwnam' => true,
                        'getpwuid' => true,
                        'getservbyname' => true,
                        'getservbyport' => true,
                        'getservent' => true,
                        'getsockname' => true,
                        'getsockopt' => true,
                        'glob' => true,
                        'gmtime' => true,
                        'goto' => true,
                        'grep' => true,
                        'hex' => true,
                        'import' => true,
                        'index' => true,
                        'int' => true,
                        'ioctl' => true,
                        'join' => true,
                        'keys' => true,
                        'kill' => true,
                        'last' => true,
                        'lc' => true,
                        'lcfirst' => true,
                        'length' => true,
                        'link' => true,
                        'listen' => true,
                        'local' => true,
                        'localtime' => true,
                        'lock' => true,
                        'log' => true,
                        'lstat' => true,
                        'map' => true,
                        'mkdir' => true,
                        'msgctl' => true,
                        'msgget' => true,
                        'msgrcv' => true,
                        'msgsnd' => true,
                        'my' => true,
                        'next' => true,
                        'no' => true,
                        'oct' => true,
                        'open' => true,
                        'opendir' => true,
                        'ord' => true,
                        'our' => true,
                        'pack' => true,
                        'package' => true,
                        'pipe' => true,
                        'pop' => true,
                        'pos' => true,
                        'print' => true,
                        'printf' => true,
                        'prototype' => true,
                        'push' => true,
                        'quotemeta' => true,
                        'rand' => true,
                        'read' => true,
                        'readdir' => true,
                        'readline' => true,
                        'readlink' => true,
                        'readpipe' => true,
                        'recv' => true,
                        'redo' => true,
                        'ref' => true,
                        'rename' => true,
                        'require' => true,
                        'reset' => true,
                        'return' => true,
                        'reverse' => true,
                        'rewinddir' => true,
                        'rindex' => true,
                        'rmdir' => true,
                        'scalar' => true,
                        'seek' => true,
                        'seekdir' => true,
                        'select' => true,
                        'semctl' => true,
                        'semget' => true,
                        'semop' => true,
                        'send' => true,
                        'setgrent' => true,
                        'sethostent' => true,
                        'setnetent' => true,
                        'setpgrp' => true,
                        'setpriority' => true,
                        'setprotoent' => true,
                        'setpwent' => true,
                        'setservent' => true,
                        'setsockopt' => true,
                        'shift' => true,
                        'shmctl' => true,
                        'shmget' => true,
                        'shmread' => true,
                        'shmwrite' => true,
                        'shutdown' => true,
                        'sin' => true,
                        'sleep' => true,
                        'socket' => true,
                        'socketpair' => true,
                        'sort' => true,
                        'splice' => true,
                        'split' => true,
                        'sprintf' => true,
                        'sqrt' => true,
                        'srand' => true,
                        'stat' => true,
                        'study' => true,
                        'sub' => true,
                        'substr' => true,
                        'symlink' => true,
                        'syscall' => true,
                        'sysopen' => true,
                        'sysread' => true,
                        'sysseek' => true,
                        'system' => true,
                        'syswrite' => true,
                        'tell' => true,
                        'telldir' => true,
                        'tie' => true,
                        'tied' => true,
                        'time' => true,
                        'times' => true,
                        'truncate' => true,
                        'uc' => true,
                        'ucfirst' => true,
                        'umask' => true,
                        'undef' => true,
                        'unlink' => true,
                        'unpack' => true,
                        'unshift' => true,
                        'untie' => true,
                        'use' => true,
                        'utime' => true,
                        'values' => true,
                        'vec' => true,
                        'wait' => true,
                        'waitpid' => true,
                        'wantarray' => true,
                        'warn' => true,
                        'write' => true,
                        'y' => true,
                    ),
                ),
                'missingreserved' => 
                array (
                    'name' => 'missingreserved',
                    'innerGroup' => 'reserved',
                    'case' => true,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'new' => true,
                    ),
                ),
                'flowcontrol' => 
                array (
                    'name' => 'flowcontrol',
                    'innerGroup' => 'reserved',
                    'case' => true,
                    'inherits' => 'identifier',
                    'match' => 
                    array (
                        'if' => true,
                        'else' => true,
                        'elsif' => true,
                        'while' => true,
                        'unless' => true,
                        'for' => true,
                        'foreach' => true,
                        'until' => true,
                        'do' => true,
                        'continue' => true,
                        'not' => true,
                        'or' => true,
                        'and' => true,
                        'eq' => true,
                        'ne' => true,
                        'gt' => true,
                        'lt' => true,
                    ),
                ),
            ),
            'blocks' => 
            array (
                'interpreter' => 
                array (
                    'name' => 'interpreter',
                    'case' => true,
                    'innerGroup' => 'special',
                    'match' => '/^(#!)(.*)/m',
                    'multiline' => false,
                    'partClass' => 
                    array (
                        1 => 'special',
                        2 => 'string',
                    ),
                    'type' => 'block',
                    'order' => 0,
                ),
                'pod' => 
                array (
                    'name' => 'pod',
                    'case' => true,
                    'innerGroup' => 'comment',
                    'delimGroup' => 'comment',
                    'start' => '/^=\\w+/m',
                    'end' => '/^=cut[^\\n]*/m',
                    'remember' => false,
                    'startBOL' => true,
                    'endBOL' => true,
                    'type' => 'region',
                    'order' => 1,
                ),
                'block' => 
                array (
                    'name' => 'block',
                    'case' => true,
                    'innerGroup' => 'code',
                    'delimGroup' => 'brackets',
                    'start' => '/\\{/',
                    'end' => '/\\}/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 2,
                    'lookfor' => 
                    array (
                        0 => 'interpreter',
                        1 => 'pod',
                        2 => 'block',
                        3 => 'brackets',
                        4 => 'sqbrackets',
                        5 => 'usestatement',
                        6 => 'packagereference',
                        7 => 'q-w-q-statement',
                        8 => 'qstatement',
                        9 => 'comment',
                        10 => 'dblregexprver1',
                        11 => 'dblregexprver2',
                        12 => 'regexp',
                        13 => 'specialvar',
                        14 => 'var',
                        15 => 'containedvar',
                        16 => 'var2',
                        17 => 'classvar',
                        18 => 'curlyvar',
                        19 => 'exec',
                        20 => 'strsingle',
                        21 => 'strdouble',
                        22 => 'identifier',
                        23 => 'number',
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
                        0 => 'interpreter',
                        1 => 'pod',
                        2 => 'block',
                        3 => 'brackets',
                        4 => 'sqbrackets',
                        5 => 'usestatement',
                        6 => 'packagereference',
                        7 => 'q-w-q-statement',
                        8 => 'qstatement',
                        9 => 'comment',
                        10 => 'dblregexprver1',
                        11 => 'dblregexprver2',
                        12 => 'regexp',
                        13 => 'bracketsvars',
                        14 => 'specialvar',
                        15 => 'var',
                        16 => 'containedvar',
                        17 => 'var2',
                        18 => 'classvar',
                        19 => 'curlyvar',
                        20 => 'exec',
                        21 => 'strsingle',
                        22 => 'strdouble',
                        23 => 'identifier',
                        24 => 'number',
                    ),
                ),
                'sqbrackets' => 
                array (
                    'name' => 'sqbrackets',
                    'case' => true,
                    'innerGroup' => 'code',
                    'delimGroup' => 'brackets',
                    'start' => '/\\[/',
                    'end' => '/\\]/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 4,
                    'lookfor' => 
                    array (
                        0 => 'interpreter',
                        1 => 'pod',
                        2 => 'block',
                        3 => 'brackets',
                        4 => 'sqbrackets',
                        5 => 'usestatement',
                        6 => 'packagereference',
                        7 => 'q-w-q-statement',
                        8 => 'qstatement',
                        9 => 'comment',
                        10 => 'dblregexprver1',
                        11 => 'dblregexprver2',
                        12 => 'regexp',
                        13 => 'specialvar',
                        14 => 'var',
                        15 => 'containedvar',
                        16 => 'var2',
                        17 => 'classvar',
                        18 => 'curlyvar',
                        19 => 'exec',
                        20 => 'strsingle',
                        21 => 'strdouble',
                        22 => 'identifier',
                        23 => 'number',
                    ),
                ),
                'usestatement' => 
                array (
                    'name' => 'usestatement',
                    'case' => true,
                    'innerGroup' => 'special',
                    'match' => '/(use)\\s+([\\w:]*)/',
                    'multiline' => false,
                    'partClass' => 
                    array (
                        1 => 'reserved',
                        2 => 'special',
                    ),
                    'type' => 'block',
                    'order' => 5,
                ),
                'packagereference' => 
                array (
                    'name' => 'packagereference',
                    'case' => true,
                    'innerGroup' => 'special',
                    'match' => '/[& ](\\w{2,}::)+\\w{2,}/',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 6,
                ),
                'q-w-q-statement' => 
                array (
                    'name' => 'q-w-q-statement',
                    'case' => true,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/\\b(q[wq]\\s*((\\{)|(\\()|(\\[)|(\\<)|([\\W\\S])))(?=(.*)((?(3)\\})(?(4)\\))(?(5)\\])(?(6)\\>)(?(7)\\7)))/Us',
                    'end' => '/%b2%/',
                    'remember' => true,
                    'type' => 'region',
                    'order' => 7,
                    'lookfor' => 
                    array (
                        0 => 'specialvar',
                        1 => 'containedvar',
                        2 => 'curlyvar',
                        3 => 'descaped',
                    ),
                ),
                'qstatement' => 
                array (
                    'name' => 'qstatement',
                    'case' => true,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/\\b(q\\s*((\\{)|(\\()|(\\[)|(\\<)|([\\W\\S])))(?=(.*)((?(3)\\})(?(4)\\))(?(5)\\])(?(6)\\>)(?(7)\\7)))/Us',
                    'end' => '/%b2%/',
                    'remember' => true,
                    'type' => 'region',
                    'order' => 8,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
                    ),
                ),
                'comment' => 
                array (
                    'name' => 'comment',
                    'case' => true,
                    'innerGroup' => 'comment',
                    'match' => '/#.*/',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 9,
                ),
                'dblregexprver1' => 
                array (
                    'name' => 'dblregexprver1',
                    'case' => true,
                    'innerGroup' => 'string',
                    'match' => '/(s|tr) ([|#~`!@$%^&*-+=\\\\;:\'",.\\/?])  ((\\\\.|[^\\\\])*?) (\\2)((\\\\.|[^\\\\])*?)(\\2[ecgimosx]*)/x',
                    'multiline' => false,
                    'partClass' => 
                    array (
                        1 => 'quotes',
                        2 => 'quotes',
                        3 => 'string',
                        5 => 'quotes',
                        6 => 'string',
                        8 => 'quotes',
                    ),
                    'type' => 'block',
                    'order' => 10,
                ),
                'dblregexprver2' => 
                array (
                    'name' => 'dblregexprver2',
                    'case' => true,
                    'innerGroup' => 'string',
                    'match' => '/(m) ([|#~`!@$%^&*-+=\\\\;:\'",.\\/?])  ((\\\\.|[^\\\\])*?) (\\2[ecgimosx]*)/x',
                    'multiline' => false,
                    'partClass' => 
                    array (
                        1 => 'quotes',
                        2 => 'quotes',
                        3 => 'string',
                        5 => 'quotes',
                    ),
                    'type' => 'block',
                    'order' => 11,
                ),
                'regexp' => 
                array (
                    'name' => 'regexp',
                    'case' => true,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/ \\//',
                    'end' => '/\\/[cgimosx]*/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 12,
                    'lookfor' => 
                    array (
                        0 => 'reescaped',
                    ),
                ),
                'reescaped' => 
                array (
                    'name' => 'reescaped',
                    'case' => true,
                    'innerGroup' => 'string',
                    'match' => '/\\\\\\//',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 13,
                ),
                'bracketsvars' => 
                array (
                    'name' => 'bracketsvars',
                    'case' => false,
                    'innerGroup' => 'string',
                    'match' => '/([a-z1-9_]+)(\\s*=>)/i',
                    'multiline' => false,
                    'partClass' => 
                    array (
                        1 => 'string',
                        2 => 'code',
                    ),
                    'type' => 'block',
                    'order' => 14,
                ),
                'specialvar' => 
                array (
                    'name' => 'specialvar',
                    'case' => true,
                    'innerGroup' => 'var',
                    'match' => '/\\$#?[1-9\'`@!]/',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 15,
                ),
                'var' => 
                array (
                    'name' => 'var',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/(\\$#?|[@%*])([a-z1-9_]+::)*([a-z1-9_]+|\\^(?-i)[A-Z]?(?i))/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 16,
                ),
                'containedvar' => 
                array (
                    'name' => 'containedvar',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/\\$([a-z1-9_]+|\\^(?-i)[A-Z]?(?i))/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 17,
                ),
                'var2' => 
                array (
                    'name' => 'var2',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/(&|\\w+)\'[\\w_\']+\\b/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 18,
                ),
                'classvar' => 
                array (
                    'name' => 'classvar',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/(\\{)([a-z1-9]+)(\\})/i',
                    'multiline' => false,
                    'partClass' => 
                    array (
                        1 => 'brackets',
                        2 => 'var',
                        3 => 'brackets',
                    ),
                    'type' => 'block',
                    'order' => 19,
                ),
                'curlyvar' => 
                array (
                    'name' => 'curlyvar',
                    'case' => false,
                    'innerGroup' => 'var',
                    'match' => '/[\\$@%]#?\\{[a-z1-9]+\\}/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 20,
                ),
                'exec' => 
                array (
                    'name' => 'exec',
                    'case' => true,
                    'innerGroup' => 'string',
                    'delimGroup' => 'quotes',
                    'start' => '/`/',
                    'end' => '/`/',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 21,
                    'lookfor' => 
                    array (
                        0 => 'specialvar',
                        1 => 'containedvar',
                        2 => 'curlyvar',
                        3 => 'escaped',
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
                    'order' => 22,
                    'lookfor' => 
                    array (
                        0 => 'escaped',
                    ),
                ),
                'escaped' => 
                array (
                    'name' => 'escaped',
                    'case' => true,
                    'innerGroup' => 'special',
                    'match' => '/\\\\\\\\|\\\\"|\\\\\'|\\\\`/',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 23,
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
                    'order' => 24,
                    'lookfor' => 
                    array (
                        0 => 'specialvar',
                        1 => 'containedvar',
                        2 => 'curlyvar',
                        3 => 'descaped',
                    ),
                ),
                'descaped' => 
                array (
                    'name' => 'descaped',
                    'case' => true,
                    'innerGroup' => 'special',
                    'match' => '/\\\\[\\\\"\'`tnr\\$\\{@]/',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 25,
                ),
                'identifier' => 
                array (
                    'name' => 'identifier',
                    'case' => false,
                    'innerGroup' => 'identifier',
                    'match' => '/[a-z_]\\w*/i',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 26,
                ),
                'number' => 
                array (
                    'name' => 'number',
                    'case' => true,
                    'innerGroup' => 'number',
                    'match' => '/\\d*\\.?\\d+/',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 27,
                ),
            ),
            'toplevel' => 
            array (
                0 => 'interpreter',
                1 => 'pod',
                2 => 'block',
                3 => 'brackets',
                4 => 'sqbrackets',
                5 => 'usestatement',
                6 => 'packagereference',
                7 => 'q-w-q-statement',
                8 => 'qstatement',
                9 => 'comment',
                10 => 'dblregexprver1',
                11 => 'dblregexprver2',
                12 => 'regexp',
                13 => 'specialvar',
                14 => 'var',
                15 => 'containedvar',
                16 => 'var2',
                17 => 'classvar',
                18 => 'curlyvar',
                19 => 'exec',
                20 => 'strsingle',
                21 => 'strdouble',
                22 => 'identifier',
                23 => 'number',
            ),
            'case' => true,
            'defClass' => 'code',
        );

        $this->_options = $options;
    }
}

?>
