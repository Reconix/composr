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
class Hook_check_base_url
{
    /**
     * Check various input var restrictions.
     *
     * @return array List of warnings
     */
    public function run()
    {
        $warning = array();

        if (file_exists(get_file_base() . '/uploads/index.html')) {
            $test_url = get_base_url() . '/uploads/index.html'; // Should normally exist, simple static URL call
        } else {
            $test_url = static_evaluate_tempcode(build_url(array('page' => ''), '', null, false, false, true)); // But this definitely must exist
        }

        $test = cms_http_request($test_url, array('byte_limit' => 0, 'trigger_error' => false, 'no_redirect' => true)); // Should return a 200 blank, not an HTTP error or a redirect; actual data would be a Composr error

        $has_www = (strpos(get_base_url(), '://www.') !== false);
        $installing = running_script('install');

        if (in_array($test->message, array('200'))) {
            // Is okay
        }

        elseif (in_array($test->message, array('401', '403'))) {
            // Is access denied, which could happen so isn't an error from our point of iew
        }

        elseif ((running_script('install')) && ($test->message == '500')) {
            // May be the final configuration isn't placed yet by the installer
        }

        elseif (in_array($test->message, array('301', '302', '307'))) {
            // Redirect

            $a = do_lang_tempcode($installing ? '_HTTP_REDIRECT_PROBLEM_INSTALLING' : '_HTTP_REDIRECT_PROBLEM_RUNNING', escape_html(get_base_url() . '/config_editor.php'));
            $b = do_lang_tempcode($has_www ? '_WITH_WWW' : '_WITHOUT_WWW', escape_html(get_base_url() . '/config_editor.php'));
            $warning[] = do_lang_tempcode('HTTP_REDIRECT_PROBLEM', $a, $b, make_string_tempcode(escape_html($test->message)));
        }

        elseif ((in_array($test->message, array('400', '404', '500', 'no-data', '408', '502', '503', '504'))) || ($test->data === null)) {
            // Some kind of error state that we shouldn't ever be expecting

            if ($installing) {
                $a = do_lang_tempcode('_IP_FORWARDING_INSTALLING');
            } else {
                $has_ip_forwarding = !((get_option('ip_forwarding') == '0') || (get_option('ip_forwarding') == ''));
                $a = do_lang_tempcode($has_ip_forwarding ? '_IP_FORWARDING_ENABLED' : '_IP_FORWARDING_DISABLED');
            }
            $warning[] = do_lang_tempcode('IP_FORWARDING_CHANGE', $a, do_lang_tempcode('config:IP_FORWARDING'), make_string_tempcode(escape_html($test->message)));
        }

        return $warning;
    }
}
