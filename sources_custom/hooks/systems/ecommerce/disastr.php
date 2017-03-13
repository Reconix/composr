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
 * @package    disastr
 */

/**
 * Hook class.
 */
class Hook_ecommerce_disastr
{
    /**
     * Get the overall categorisation for the products handled by this eCommerce hook.
     *
     * @return ?array A map of product categorisation details (null: disabled).
     */
    public function get_product_category()
    {
        require_lang('disastr');

        return array(
            'category_name' => do_lang('DISEASES_CURES_IMMUNISATIONS_TITLE'),
            'category_description' => do_lang_tempcode('DISEASES_DESCRIPTION'),
            'category_image_url' => find_theme_image('icons/48x48/menu/disastr_log'),
        );
    }

    /**
     * Get the products handled by this eCommerce hook.
     *
     * IMPORTANT NOTE TO PROGRAMMERS: This function may depend only on the database, and not on get_member() or any GET/POST values.
     *  Such dependencies will break IPN, which works via a Guest and no dependable environment variables. It would also break manual transactions from the Admin Zone.
     *
     * @param  ?ID_TEXT $search Product being searched for (null: none).
     * @return array A map of product name to list of product details.
     */
    public function get_products($search = null)
    {
        if (!$GLOBALS['SITE_DB']->table_exists('members_diseases')) {
            return array();
        }

        require_lang('disastr');

        $products = array();

        $rows = $GLOBALS['SITE_DB']->query_select('diseases', array('*'), null, 'ORDER BY name');
        foreach ($rows as $disease) {
            $image_url = $disease['image'];
            if ($image_url != '') {
                if (url_is_local($image_url)) {
                    $image_url = get_custom_base_url() . '/' . $image_url;
                }
            }

            $products['CURE_' . strval($disease['id'])] = array(
                'item_name' => do_lang('__CURE', $disease['name'], $disease['cure']),
                'item_description' => do_lang_tempcode('_CURE', escape_html($disease['name']), escape_html($disease['cure'])),
                'item_image_url' => $image_url,

                'type' => PRODUCT_PURCHASE,
                'type_special_details' => array(),

                'price' => null,
                'currency' => get_option('currency'),
                'price_points' => $disease['cure_price'],
                'discount_points__num_points' => null,
                'discount_points__price_reduction' => null,

                'tax_code' => '0.0',
                'shipping_cost' => 0.00,
                'needs_shipping_address' => false,
            );

            $products['IMMUNISATION_' . strval($disease['id'])] = array(
                'item_name' => do_lang('__IMMUNISATION', $disease['name'], $disease['immunisation']),
                'item_description' => do_lang_tempcode('_IMMUNISATION', escape_html($disease['name']), escape_html($disease['immunisation'])),
                'item_image_url' => $image_url,

                'type' => PRODUCT_PURCHASE,
                'type_special_details' => array(),

                'price' => null,
                'currency' => get_option('currency'),
                'price_points' => $disease['immunisation_price'],
                'discount_points__num_points' => null,
                'discount_points__price_reduction' => null,

                'tax_code' => '0.0',
                'shipping_cost' => 0.00,
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
        if (is_guest($member_id)) {
            return ECOMMERCE_PRODUCT_NO_GUESTS;
        }

        $matches = array();
        if (preg_match('#^(CURE|IMMUNISATION)\_(\d+)$#', $type_code, $matches) == 0) {
            fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
        }
        $disease_id = intval($matches[2]);

        $rows = $GLOBALS['SITE_DB']->query_select('diseases', array('*'), array('id' => $disease_id), '', 1);
        if (!array_key_exists(0, $rows)) {
            return ECOMMERCE_PRODUCT_MISSING;
        }
        $disease = $rows[0];

        $member_rows = $GLOBALS['SITE_DB']->query_select('members_diseases', array('*'), array('member_id' => $member_id, 'disease_id' => $disease['id']), '', 1);
        if (array_key_exists(0, $member_rows)) {
            $member_row = $member_rows[0];
        } else {
            $member_row = array('sick' => 0, 'cure' => 0, 'immunisation' => 0);
        }

        if ($member_row['cure'] == 1) {
            return ECOMMERCE_PRODUCT_ALREADY_HAS;
        }

        if ($member_row['immunisation'] == 1) {
            return ECOMMERCE_PRODUCT_ALREADY_HAS;
        }

        if ($matches[1] == 'CURE') {
            if ($member_row['sick'] == 0) {
                return ECOMMERCE_PRODUCT_DISABLED;
            }
        }

        if ($matches[1] == 'IMMUNISATION') {
            if ($member_row['sick'] == 1) {
                return ECOMMERCE_PRODUCT_DISABLED;
            }
        }

        return ECOMMERCE_PRODUCT_AVAILABLE;
    }

    /**
     * Get fields that need to be filled in in the purchasing module.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  boolean $from_admin Whether this is being called from the Admin Zone. If so, optionally different fields may be used, including a purchase_id field for direct purchase ID input.
     * @return ?array A triple: The fields (null: none), The text (null: none), The JavaScript (null: none).
     */
    public function get_needed_fields($type_code, $from_admin = false)
    {
        return null;
    }

    /**
     * Handling of a product purchase change state.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @param  array $details Details of the product, with added keys: TXN_ID, STATUS, ORDER_STATUS.
     * @return boolean Whether the product was automatically dispatched (if not then hopefully this function sent a staff notification).
     */
    public function actualiser($type_code, $purchase_id, $details)
    {
        if ($details['STATUS'] != 'Completed') {
            return false;
        }

        require_lang('disastr');

        $matches = array();
        if (preg_match('#^(CURE|IMMUNISATION)\_(\d+)$#', $type_code, $matches) == 0) {
            fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
        }
        $disease_id = intval($matches[2]);

        $member_id = intval($purchase_id);

        $rows = $GLOBALS['SITE_DB']->query_select('diseases', array('*'), array('id' => $disease_id), '', 1);
        if (!array_key_exists(0, $rows)) {
            warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
        }
        $disease_row = $rows[0];

        $member_rows = $GLOBALS['SITE_DB']->query_select('members_diseases', array('*'), array('member_id' => $member_id, 'disease_id' => $disease_id), '', 1);

        $map = array(
            'member_id' => $member_id,
            'disease_id' => $disease_id,
        );
        if ($matches[1] == 'CURE') {
            $map['cure'] = 1;
            $map['sick'] = 0;
            if (!array_key_exists(0, $member_rows)) {
                $map['immunisation'] = 0; // This code should not actually be runnable
            }
        } elseif ($matches[1] == 'IMMUNISATION') {
            $map['immunisation'] = 1;
            if (!array_key_exists(0, $member_rows)) {
                $map['cure'] = 0;
                $map['sick'] = 0;
            }
        }

        $member_rows = $GLOBALS['SITE_DB']->query_select('members_diseases', array('*'), array('member_id' => $member_id, 'disease_id' => $disease_id), '', 1);
        if (array_key_exists(0, $member_rows)) {
            $GLOBALS['SITE_DB']->query_update('members_diseases', $map, array('member_id' => $member_id, 'disease_id' => $disease_id), '', 1);
        } else {
            $GLOBALS['SITE_DB']->query_insert('members_diseases', $map);
        }

        if ($matches[1] == 'CURE') {
            $GLOBALS['SITE_DB']->query_insert('ecom_sales', array('date_and_time' => time(), 'member_id' => $member_id, 'details' => do_lang('CURE', null, null, null, get_site_default_lang()), 'details2' => $disease_row['cure'], 'txn_id' => $details['TXN_ID']));
        } elseif ($matches[1] == 'IMMUNISATION') {
            $GLOBALS['SITE_DB']->query_insert('ecom_sales', array('date_and_time' => time(), 'member_id' => $member_id, 'details' => do_lang('IMMUNISATION', null, null, null, get_site_default_lang()), 'details2' => $disease_row['immunisation'], 'txn_id' => $details['TXN_ID']));
        }

        // There's an urgency, so show an instant message (plus buying via points, so will definitely be seen)
        if ($matches[1] == 'IMMUNISATION') {
            $result = do_lang_tempcode('IMMUNISATION_CONGRATULATIONS');
        } else {
            $result = do_lang_tempcode('CURE_CONGRATULATIONS');
        }
        global $ECOMMERCE_SPECIAL_SUCCESS_MESSAGE;
        $ECOMMERCE_SPECIAL_SUCCESS_MESSAGE = $result;

        return true;
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
        return intval($purchase_id);
    }
}
