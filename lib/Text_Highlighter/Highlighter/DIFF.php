<?php
/**
 * Auto-generated class. DIFF syntax highlighting
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
 * Auto-generated class. DIFF syntax highlighting
 *
 * @author Andrey Demenev <demenev@on-line.jar.ru>
 * @category   Text
 * @package Text_Highlighter
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_DIFF extends Text_Highlighter
{    /**
     * PHP4 Compatible Constructor
     *
     * @param array  $options
     * @access public
     */
    function Text_Highlighter_DIFF($options=array())
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
                'noNewLine' => 
                array (
                    'name' => 'noNewLine',
                    'case' => true,
                    'innerGroup' => 'special',
                    'match' => '/^\\\\\\sNo\\snewline.+$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 0,
                ),
                'diffSeparator' => 
                array (
                    'name' => 'diffSeparator',
                    'case' => true,
                    'innerGroup' => 'code',
                    'match' => '/^\\-\\-\\-$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 1,
                ),
                'diffCmdLine' => 
                array (
                    'name' => 'diffCmdLine',
                    'case' => true,
                    'innerGroup' => 'var',
                    'match' => '/^(diff\\s+\\-|Only\\s+|Index).*$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 2,
                ),
                'diffFiles' => 
                array (
                    'name' => 'diffFiles',
                    'case' => true,
                    'innerGroup' => 'reserved',
                    'match' => '/^(\\-\\-\\-|\\+\\+\\+)\\s.+$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 3,
                ),
                'contextOrg' => 
                array (
                    'name' => 'contextOrg',
                    'case' => true,
                    'innerGroup' => 'quotes',
                    'match' => '/^\\*.*$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 4,
                ),
                'contextNew' => 
                array (
                    'name' => 'contextNew',
                    'case' => true,
                    'innerGroup' => 'string',
                    'match' => '/^\\+.*$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 5,
                ),
                'contextChg' => 
                array (
                    'name' => 'contextChg',
                    'case' => true,
                    'innerGroup' => 'inlinedoc',
                    'match' => '/^!.*$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 6,
                ),
                'defOrg' => 
                array (
                    'name' => 'defOrg',
                    'case' => true,
                    'innerGroup' => 'quotes',
                    'match' => '/^\\<\\s.*$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 7,
                ),
                'defNew' => 
                array (
                    'name' => 'defNew',
                    'case' => true,
                    'innerGroup' => 'string',
                    'match' => '/^\\>\\s.*$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 8,
                ),
                'defChg' => 
                array (
                    'name' => 'defChg',
                    'case' => true,
                    'innerGroup' => 'code',
                    'match' => '/^\\d+(\\,\\d+)?[acd]\\d+(,\\d+)?$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 9,
                ),
                'uniOrg' => 
                array (
                    'name' => 'uniOrg',
                    'case' => true,
                    'innerGroup' => 'quotes',
                    'match' => '/^\\-.*$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 10,
                ),
                'uniNew' => 
                array (
                    'name' => 'uniNew',
                    'case' => true,
                    'innerGroup' => 'string',
                    'match' => '/^\\+.*$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 11,
                ),
                'uniChg' => 
                array (
                    'name' => 'uniChg',
                    'case' => true,
                    'innerGroup' => 'code',
                    'match' => '/^@@.+@@$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 12,
                ),
                'normOrg' => 
                array (
                    'name' => 'normOrg',
                    'case' => true,
                    'innerGroup' => 'code',
                    'match' => '/^d\\d+\\s\\d+$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 13,
                ),
                'normNew' => 
                array (
                    'name' => 'normNew',
                    'case' => true,
                    'innerGroup' => 'var',
                    'delimGroup' => 'code',
                    'start' => '/^a\\d+\\s\\d+$/m',
                    'end' => '/(?=^[ad]\\d+\\s\\d+)/m',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 14,
                ),
                'edNew' => 
                array (
                    'name' => 'edNew',
                    'case' => true,
                    'innerGroup' => 'string',
                    'delimGroup' => 'code',
                    'start' => '/^(\\d+)(,\\d+)?(a)$/m',
                    'end' => '/^(\\.)$/m',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 15,
                ),
                'edChg' => 
                array (
                    'name' => 'edChg',
                    'case' => true,
                    'innerGroup' => 'inlinedoc',
                    'delimGroup' => 'code',
                    'start' => '/^(\\d+)(,\\d+)?(c)$/m',
                    'end' => '/^(\\.)$/m',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 16,
                ),
                'edDel' => 
                array (
                    'name' => 'edDel',
                    'case' => true,
                    'innerGroup' => 'code',
                    'match' => '/^(\\d+)(,\\d+)?(d)$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 17,
                ),
                'fedNew' => 
                array (
                    'name' => 'fedNew',
                    'case' => true,
                    'innerGroup' => 'string',
                    'delimGroup' => 'code',
                    'start' => '/^a(\\d+)(\\s\\d+)?$/m',
                    'end' => '/^(\\.)$/m',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 18,
                ),
                'fedChg' => 
                array (
                    'name' => 'fedChg',
                    'case' => true,
                    'innerGroup' => 'inlinedoc',
                    'delimGroup' => 'code',
                    'start' => '/^c(\\d+)(\\s\\d+)?$/m',
                    'end' => '/^(\\.)$/m',
                    'remember' => false,
                    'type' => 'region',
                    'order' => 19,
                ),
                'fedDel' => 
                array (
                    'name' => 'fedDel',
                    'case' => true,
                    'innerGroup' => 'code',
                    'match' => '/^d(\\d+)(\\s\\d+)?$/m',
                    'multiline' => false,
                    'type' => 'block',
                    'order' => 20,
                ),
            ),
            'toplevel' => 
            array (
                    0 => 'noNewLine',
                    1 => 'diffSeparator',
                    2 => 'diffCmdLine',
                    3 => 'diffFiles',
                    4 => 'contextOrg',
                    5 => 'contextNew',
                    6 => 'contextChg',
                    7 => 'defOrg',
                    8 => 'defNew',
                    9 => 'defChg',
                    10 => 'uniOrg',
                    11 => 'uniNew',
                    12 => 'uniChg',
                    13 => 'normOrg',
                14 => 'normNew',
                15 => 'edNew',
                16 => 'edChg',
                17 => 'edDel',
                18 => 'fedNew',
                19 => 'fedChg',
                20 => 'fedDel',
            ),
            'case' => true,
            'defClass' => 'default',
        );

        $this->_options = $options;
    }
}

?>
