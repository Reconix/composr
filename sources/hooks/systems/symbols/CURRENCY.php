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
class Hook_symbol_CURRENCY
{
    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @return string Result
     */
    public function run($param)
    {
        $value = '';

        if (addon_installed('ecommerce')) {
            if (isset($param[0])) {
                require_code('currency');
                $amount = floatval(str_replace(',', '', $param[0]));
                $from_currency = ((isset($param[1])) && ($param[1] != '')) ? $param[1] : get_option('currency');
                $to_currency = ((isset($param[2])) && ($param[2] != '')) ? $param[2] : null;
                $value = currency_convert($amount, $from_currency, $to_currency, true);
                if (is_null($value)) {
                    $value = $param[0] . ' ' . $from_currency . '<!--' . do_lang('INTERNAL_ERROR') . '-->';
                }
            } else {
                $value = get_option('currency');
            }
        }

        return $value;
    }
}
