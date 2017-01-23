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
 * @package    ecommerce
 */

/**
 * Hook class.
 */
class Hook_ecommerce_community_billboard
{
    /**
     * Get the overall categorisation for the products handled by this eCommerce hook.
     *
     * @return ?array A map of product categorisation details (null: disabled).
     */
    public function get_product_category()
    {
        require_lang('community_billboard');

        return array(
            'category_name' => do_lang('COMMUNITY_BILLBOARD_MESSAGE'),
            'category_description' => do_lang_tempcode('COMMUNITY_BILLBOARD_MESSAGE_DESCRIPTION'),
            'category_image_url' => find_theme_image('icons/48x48/menu/adminzone/audit/community_billboard'),
        );
    }

    /**
     * Get the products handled by this eCommerce hook.
     *
     * IMPORTANT NOTE TO PROGRAMMERS: This function may depend only on the database, and not on get_member() or any GET/POST values.
     *  Such dependencies will break IPN, which works via a Guest and no dependable environment variables. It would also break manual transactions from the Admin Zone.
     *
     * @param  boolean $site_lang Whether to make sure the language for item_name is the site default language (crucial for when we read/go to third-party sales systems and use the item_name as a key).
     * @param  ?ID_TEXT $search Product being searched for (null: none).
     * @param  boolean $search_item_names Whether $search refers to the item name rather than the product codename.
     * @return array A map of product name to list of product details.
     */
    public function get_products($site_lang = false, $search = null, $search_item_names = false)
    {
        if (!$GLOBALS['SITE_DB']->table_exists('community_billboard')) {
            return array();
        }

        require_lang('community_billboard');

        $day_price = intval(get_option('community_billboard'));

        $products = array();

        foreach (array(1, 3, 5, 10, 20, 31, 90) as $days) {
            $products['COMMUNITY_BILLBOARD_' . strval($days)] = array(
                'item_name' => do_lang('COMMUNITY_BILLBOARD_MESSAGE_FOR_DAYS', integer_format($days), null, null, $site_lang ? get_site_default_lang() : user_lang()),
                'item_description' => new Tempcode(),
                'item_image_url' => '',

                'type' => PRODUCT_PURCHASE,
                'type_special_details' => array(),

                'price' => null,
                'currency' => get_option('currency'),
                'price_points' => $day_price * $days,
                'discount_points__num_points' => null,
                'discount_points__price_reduction' => null,

                'needs_shipping_address' => false,
            );
        }

        return $products;
    }

    /**
     * Check whether the product codename is available for purchase by the member.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  MEMBER $member_id The member we are checking against.
     * @param  integer $req_quantity The number required.
     * @param  boolean $must_be_listed Whether the product must be available for public listing.
     * @return integer The availability code (a ECOMMERCE_PRODUCT_* constant).
     */
    public function is_available($type_code, $member_id, $req_quantity = 1, $must_be_listed = false)
    {
        if (get_option('is_on_community_billboard_buy') == '0') {
            return ECOMMERCE_PRODUCT_DISABLED;
        }

        if (is_guest($member_id)) {
            return ECOMMERCE_PRODUCT_NO_GUESTS;
        }

        return ECOMMERCE_PRODUCT_AVAILABLE;
    }

    /**
     * Get the message for use in the purchasing module.
     *
     * @param  string $type_code The product in question.
     * @return Tempcode The message.
     */
    public function get_message($type_code)
    {
        require_lang('community_billboard');

        $queue = $GLOBALS['SITE_DB']->query_select_value('community_billboard', 'SUM(days) AS days', array('activation_time' => null));
        if ($queue === null) {
            $queue = 0;
        }
        $cost = intval(get_option('community_billboard'));

        return do_template('ECOM_PRODUCT_COMMUNITY_BILLBOARD', array('_GUID' => '92d51c5b87745c31397d9165595262d3', 'QUEUE' => integer_format($queue), 'COST' => integer_format($cost)));
    }

    /**
     * Get fields that need to be filled in in the purchasing module.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @return ?array A triple: The fields (null: none), The text (null: none), The JavaScript (null: none).
     */
    public function get_needed_fields($type_code)
    {
        require_lang('community_billboard');

        require_code('form_templates');

        $fields = new Tempcode();
        $fields->attach(form_input_line_comcode(do_lang_tempcode('MESSAGE'), do_lang_tempcode('MESSAGE_DESCRIPTION'), 'message', '', true));

        return array($fields, null, null);
    }

    /**
     * Get the filled in fields and do something with them.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @return array A pair: The purchase ID, a confirmation box to show (null for no specific confirmation).
     */
    public function handle_needed_fields($type_code)
    {
        $member_id = get_member();
        $message = post_param_string('message');

        $e_details = json_encode(array($member_id, $message));

        $purchase_id = strval($GLOBALS['SITE_DB']->query_insert('ecom_sales_expecting', array('e_details' => $e_details, 'e_time' => time()), true));
        return array($purchase_id, null);
    }

    /**
     * Handling of a product purchase change state.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @param  array $details Details of the product, with added keys: TXN_ID, PAYMENT_STATUS, ORDER_STATUS.
     */
    public function actualiser($type_code, $purchase_id, $details)
    {
        if ($details['PAYMENT_STATUS'] != 'Completed') {
            return;
        }

        require_lang('community_billboard');

        $days = intval(preg_replace('#^COMMUNITY_BILLBOARD\_#', '', $type_code));

        $e_details = $GLOBALS['SITE_DB']->query_select_value('ecom_sales_expecting', 'e_details', array('id' => intval($purchase_id)));
        list($member_id, $message) = json_decode($e_details);

        // Add this to the database
        $map = array(
            'notes' => '',
            'activation_time' => null,
            'active_now' => 0,
            'member_id' => $member_id,
            'days' => $days,
            'order_time' => time(),
        );
        $map += insert_lang_comcode('the_message', $message, 2);
        $GLOBALS['SITE_DB']->query_insert('community_billboard', $map);

        $GLOBALS['SITE_DB']->query_insert('ecom_sales', array('date_and_time' => time(), 'member_id' => $member_id, 'details' => do_lang('COMMUNITY_BILLBOARD_MESSAGE', null, null, null, get_site_default_lang()), 'details2' => strval($days), 'transaction_id' => $details['TXN_ID']));

        // Mail off the notice
        require_code('notifications');
        $_url = build_url(array('page' => 'admin_community_billboard'), get_module_zone('admin_community_billboard'), null, false, false, true);
        $manage_url = $_url->evaluate();
        dispatch_notification('ecom_request_community_billboard', null, do_lang('TITLE_NEWCOMMUNITY_BILLBOARD', null, null, null, get_site_default_lang()), do_notification_lang('MAIL_COMMUNITY_BILLBOARD_TEXT', $message, comcode_escape($manage_url), null, get_site_default_lang()));
    }

    /**
     * Get the member who made the purchase.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @return ?MEMBER The member ID (null: none).
     */
    public function member_for($type_code, $purchase_id)
    {
        $e_details = $GLOBALS['SITE_DB']->query_select_value('ecom_sales_expecting', 'e_details', array('id' => intval($purchase_id)));
        list($member_id) = json_decode($e_details);
        return $member_id;
    }
}
