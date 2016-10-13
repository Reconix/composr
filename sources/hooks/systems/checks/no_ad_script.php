<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_check_no_ad_script
{
    /**
     * Check various input var restrictions.
     *
     * @return array List of warnings
     */
    public function run()
    {
        $warning = array();

        if (running_script('install')) {
            $url = get_base_url() . '/install.php?type=test_blank_result';
        } else {
            $url = find_script('blank');
        }

        if (http_get_contents($url, array('trigger_error' => false, 'timeout' => 1.0)) != '') {
            $warning[] = do_lang_tempcode('INTERFERING_AD_SCRIPT');
        }

        return $warning;
    }
}
