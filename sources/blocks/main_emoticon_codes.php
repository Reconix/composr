<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_rich_media
 */

/**
 * Block class.
 */
class Block_main_emoticon_codes
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        $info['parameters'] = array('num_columns');
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled).
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = 'array(array_key_exists(\'num_columns\', $map) ? intval($map[\'num_columns\']) : 5)';
        $info['special_cache_flags'] = CACHE_AGAINST_DEFAULT | CACHE_AGAINST_PERMISSIVE_GROUPS; // Due to special emoticon codes privilege
        $info['ttl'] = (get_value('no_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60 * 2;
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters.
     * @return Tempcode The result of execution.
     */
    public function run($map)
    {
        require_code('comcode_compiler');
        require_code('comcode_renderer');

        $emoticons = $GLOBALS['FORUM_DRIVER']->find_emoticons(get_member());

        $num_columns = array_key_exists('num_columns', $map) ? intval($map['num_columns']) : 4;

        $rows = new Tempcode();

        global $EMOTICON_LEVELS;

        $num = count($emoticons);
        $keys = array_keys($emoticons);
        $values = array_values($emoticons);
        $i = 0;
        while ($i < $num) {
            $columns = array();

            for ($j = 0; $j < $num_columns; $j++) {
                if (!isset($keys[$i])) {
                    $columns[] = array(
                        'CODE' => '',
                        'TPL' => '',
                    );
                    continue;
                }

                $code = $keys[$i];
                $imgcode = $values[$i];

                if ((is_null($EMOTICON_LEVELS)) || ($EMOTICON_LEVELS[$code] < 3)) { // If within a displayable level
                    $columns[] = array(
                        'CODE' => $code,
                        'TPL' => do_emoticon($imgcode),
                    );
                }

                $i++;
            }

            $rows->attach(do_template('BLOCK_MAIN_EMOTICON_CODES_ENTRY', array(
                '_GUID' => '9d723c17133313b327a9485aeb23aa8c',
                'COLUMNS' => $columns,
            )));
        }

        return do_template('BLOCK_MAIN_EMOTICON_CODES', array(
            '_GUID' => '56c12281d7e3662b13a7ad7d9958a65c',
            'ROWS' => $rows,
            'NUM_COLUMNS' => strval($num_columns),
        ));
    }
}
