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
class Hook_ecommerce_permission
{
    /**
     * Standard eCommerce product configuration function.
     *
     * @return ?array A tuple: list of [fields to shown, hidden fields], title for add form, add form (null: disabled)
     */
    public function config()
    {
        $fields = new Tempcode();
        $rows = $GLOBALS['SITE_DB']->query_select('ecom_prods_permissions', array('*'), null, 'ORDER BY id');
        $hidden = new Tempcode();
        $out = array();
        foreach ($rows as $i => $row) {
            $fields = new Tempcode();
            $hidden = new Tempcode();
            $hours = $row['p_hours'];
            if ($hours == 400000) {
                $hours = null; // LEGACY: Around 100 years, but meaning unlimited
            }
            $fields->attach($this->_get_fields('_' . strval($i), get_translated_text($row['p_title']), get_translated_text($row['p_description']), $row['p_enabled'], $row['p_price'], $row['p_price_points'], $hours, $row['p_type'], $row['p_privilege'], $row['p_zone'], $row['p_page'], $row['p_module'], $row['p_category'], get_translated_text($row['p_mail_subject']), get_translated_text($row['p_mail_body'])));
            $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '4055cbfc1c94723f4ad72a80ede0b554', 'TITLE' => do_lang_tempcode('ACTIONS'))));
            $fields->attach(form_input_tick(do_lang_tempcode('DELETE'), do_lang_tempcode('DESCRIPTION_DELETE'), 'delete_permission_' . strval($i), false));
            $hidden->attach(form_input_hidden('permission_' . strval($i), strval($row['id'])));
            $out[] = array($fields, $hidden, do_lang_tempcode('_EDIT_PERMISSION_PRODUCT', escape_html(get_translated_text($row['p_title']))));
        }

        return array(
            array($out, do_lang_tempcode('ADD_NEW_PERMISSION_PRODUCT'), $this->_get_fields(), do_lang_tempcode('PERMISSION_PRODUCT_DESCRIPTION')),
        );
    }

    /**
     * Get fields for adding/editing one of these.
     *
     * @param  string $name_suffix What to place onto the end of the field name
     * @param  SHORT_TEXT $title Title
     * @param  LONG_TEXT $description Description
     * @param  BINARY $enabled Whether it is enabled
     * @param  ?REAL $price The price (null: not set)
     * @param  ID_TEXT $tax_code The tax code
     * @param  ?integer $price_points The price in points (null: not set)
     * @param  ?integer $hours Number of hours for it to last for (null: unlimited)
     * @param  ID_TEXT $type Permission scope 'type'
     * @set member_privileges member_category_access member_page_access member_zone_access
     * @param  ID_TEXT $privilege Permission scope 'privilege'
     * @param  ID_TEXT $zone Permission scope 'zone'
     * @param  ID_TEXT $page Permission scope 'page'
     * @param  ID_TEXT $module Permission scope 'module'
     * @param  ID_TEXT $category Permission scope 'category'
     * @param  SHORT_TEXT $mail_subject Confirmation mail subject
     * @param  LONG_TEXT $mail_body Confirmation mail body
     * @return Tempcode The fields
     */
    protected function _get_fields($name_suffix = '', $title = '', $description = '', $enabled = 1, $price = 0.00, $tax_code = '0.0', $price_points = null, $hours = null, $type = 'member_privileges', $privilege = '', $zone = '', $page = '', $module = '', $category = '', $mail_subject = '', $mail_body = '')
    {
        require_lang('points');

        $fields = new Tempcode();

        $fields->attach(form_input_line(do_lang_tempcode('TITLE'), do_lang_tempcode('DESCRIPTION_TITLE'), 'permission_title' . $name_suffix, $title, true));
        $fields->attach(form_input_text(do_lang_tempcode('DESCRIPTION'), do_lang_tempcode('DESCRIPTION_DESCRIPTION'), 'permission_description' . $name_suffix, $description, true));
        $fields->attach(form_input_float(do_lang_tempcode('PRICE'), do_lang_tempcode('DESCRIPTION_PRICE'), 'permission_price' . $name_suffix, $price, false));
        $fields->attach(form_input_tax_code(do_lang_tempcode(get_option('tax_system')), do_lang_tempcode('DESCRIPTION_TAX_CODE'), 'permission_tax_code' . $name_suffix, $tax_code, true));
        if (addon_installed('points')) {
            $fields->attach(form_input_integer(do_lang_tempcode('PRICE_POINTS'), do_lang_tempcode('DESCRIPTION_PRICE_POINTS'), 'permission_price_points' . $name_suffix, $price_points, false));
        }
        $fields->attach(form_input_integer(do_lang_tempcode('PERMISSION_HOURS'), do_lang_tempcode('DESCRIPTION_PERMISSION_HOURS'), 'permission_hours' . $name_suffix, $hours, false));
        $fields->attach(form_input_tick(do_lang_tempcode('ENABLED'), '', 'permission_enabled' . $name_suffix, $enabled == 1));

        $types = new Tempcode();
        $_types = array('member_privileges', 'member_zone_access', 'member_page_access', 'member_category_access');
        foreach ($_types as $_type) {
            $types->attach(form_input_list_entry($_type, $type == $_type, do_lang_tempcode('PERM_TYPE_' . $_type)));
        }
        $fields->attach(form_input_list(do_lang_tempcode('PERMISSION_SCOPE_type'), do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_type'), 'permission_type' . $name_suffix, $types));

        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => 'c1ee1d8ff171d8de6cd5ed14b5a59afb', 'SECTION_HIDDEN' => false, 'TITLE' => do_lang_tempcode('SETTINGS'))));

        require_all_lang();

        $privileges = new Tempcode();
        $temp = form_input_list_entry('', false, do_lang_tempcode('NA_EM'));
        $privileges->attach($temp);
        $_privileges = $GLOBALS['SITE_DB']->query_select('privilege_list', array('*'), null, 'ORDER BY p_section,the_name');
        $__privileges = array();
        foreach ($_privileges as $_privilege) {
            $_pt_name = do_lang('PRIVILEGE_' . $_privilege['the_name'], null, null, null, null, false);
            if ($_pt_name === null) {
                continue;
            }
            $__privileges[$_privilege['the_name']] = $_pt_name;
        }
        natsort($__privileges);
        foreach (array_keys($__privileges) as $__privilege) {
            $pt_name = do_lang_tempcode('PRIVILEGE_' . $__privilege);
            $temp = form_input_list_entry($__privilege, $__privilege == $privilege, $pt_name);
            $privileges->attach($temp);
        }
        $fields->attach(form_input_list(do_lang_tempcode('PERMISSION_SCOPE_privilege'), do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_privilege'), 'permission_privilege' . $name_suffix, $privileges, null, false, false));

        $zones = new Tempcode();
        //$zones->attach(form_input_list_entry('', false, do_lang_tempcode('NA_EM')));      Will always scope to a zone. Welcome zone would be '' anyway, so we're simplifying the code by having a zone setting which won't hurt anyway
        require_code('zones2');
        require_code('zones3');
        $zones->attach(create_selection_list_zones($zone));
        $fields->attach(form_input_list(do_lang_tempcode('PERMISSION_SCOPE_zone'), do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_zone'), 'permission_zone' . $name_suffix, $zones, null, false, false));

        $pages = new Tempcode();
        $temp = form_input_list_entry('', false, do_lang_tempcode('NA_EM'));
        $pages->attach($temp);
        $_zones = find_all_zones();
        $_pages = array();
        foreach ($_zones as $_zone) {
            $_pages += find_all_pages_wrap($_zone);
        }
        foreach (array_keys($_pages) as $_page) {
            if (is_integer($_page)) {
                $_page = strval($_page); // PHP array combining weirdness
            }
            $temp = form_input_list_entry($_page, $page == $_page);
            $pages->attach($temp);
        }
        $fields->attach(form_input_list(do_lang_tempcode('PERMISSION_SCOPE_page'), do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_page'), 'privilege_page' . $name_suffix, $pages, null, false, false));

        $modules = new Tempcode();
        $temp = form_input_list_entry('', false, do_lang_tempcode('NA_EM'));
        $modules->attach($temp);
        $_modules = find_all_hooks('systems', 'module_permissions');
        foreach (array_keys($_modules) as $_module) {
            $temp = form_input_list_entry($_module, $_module == $module);
            $modules->attach($temp);
        }
        $fields->attach(form_input_list(do_lang_tempcode('PERMISSION_SCOPE_module'), do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_module'), 'permission_module' . $name_suffix, $modules, null, false, false));

        $fields->attach(form_input_line(do_lang_tempcode('PERMISSION_SCOPE_category'), do_lang_tempcode('DESCRIPTION_PERMISSION_SCOPE_category'), 'permission_category' . $name_suffix, $category, false));

        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => 'b89804ab98762d661f4337b1dfb62d46', 'SECTION_HIDDEN' => false, 'TITLE' => do_lang_tempcode('PURCHASE_MAIL'), 'HELP' => do_lang_tempcode('DESCRIPTION_PURCHASE_MAIL'))));
        $fields->attach(form_input_line(do_lang_tempcode('PURCHASE_MAIL_SUBJECT'), '', 'permission_mail_subject' . $name_suffix, $mail_subject, false));
        $fields->attach(form_input_text_comcode(do_lang_tempcode('PURCHASE_MAIL_BODY'), '', 'permission_mail_body' . $name_suffix, $mail_body, false));

        return $fields;
    }

    /**
     * Standard eCommerce product configuration save function.
     */
    public function save_config()
    {
        $i = 0;
        $rows = list_to_map('id', $GLOBALS['SITE_DB']->query_select('ecom_prods_permissions', array('*')));
        while (array_key_exists('permission_' . strval($i), $_POST)) {
            $id = post_param_integer('permission_' . strval($i));
            $title = post_param_string('permission_title_' . strval($i));
            $description = post_param_string('permission_description_' . strval($i));
            $enabled = post_param_integer('permission_enabled_' . strval($i), 0);
            $_price = post_param_string('permission_price_' . strval($i), '');
            $price = ($_price == '') ? null : float_unformat($_price);
            $tax = post_param_tax_code('permission_tax_code_' . strval($i));
            if (addon_installed('points')) {
                $price_points = post_param_integer('permission_price_points_' . strval($i), null);
            } else {
                $price_points = null;
            }
            $hours = post_param_integer('permission_hours_' . strval($i), null);
            $type = post_param_string('permission_type_' . strval($i));
            $privilege = post_param_string('permission_privilege_' . strval($i));
            $zone = post_param_string('permission_zone_' . strval($i));
            $page = post_param_string('privilege_page_' . strval($i));
            $module = post_param_string('permission_module_' . strval($i));
            $category = post_param_string('permission_category_' . strval($i));
            $mail_subject = post_param_string('permission_mail_subject_' . strval($i));
            $mail_body = post_param_string('permission_mail_body_' . strval($i));

            $delete = post_param_integer('delete_permission_' . strval($i), 0);

            $_title = $rows[$id]['p_title'];
            $_description = $rows[$id]['p_description'];
            $_mail_subject = $rows[$id]['p_mail_subject'];
            $_mail_body = $rows[$id]['p_mail_body'];

            if ($delete == 1) {
                delete_lang($_title);
                delete_lang($_description);
                delete_lang($_mail_subject);
                delete_lang($_mail_body);
                $GLOBALS['SITE_DB']->query_delete('ecom_prods_permissions', array('id' => $id), '', 1);
            } else {
                $map = array(
                    'p_enabled' => $enabled,
                    'p_price' => $price,
                    'p_tax_code' => $tax_code,
                    'p_price_points' => $price_points,
                    'p_hours' => $hours,
                    'p_type' => $type,
                    'p_privilege' => $privilege,
                    'p_zone' => $zone,
                    'p_page' => $page,
                    'p_module' => $module,
                    'p_category' => $category,
                );
                $map += lang_remap('p_title', $_title, $title);
                $map += lang_remap_comcode('p_description', $_description, $description);
                $map += lang_remap('p_mail_subject', $_mail_subject, $mail_subject);
                $map += lang_remap('p_mail_body', $_mail_body, $mail_body);
                $GLOBALS['SITE_DB']->query_update('ecom_prods_permissions', $map, array('id' => $id), '', 1);
            }
            $i++;
        }

        $title = post_param_string('permission_title', null);
        if ($title !== null) {
            $description = post_param_string('permission_description');
            $enabled = post_param_integer('permission_enabled', 0);
            $_price = post_param_string('permission_price', '');
            $price = ($_price == '') ? null : float_unformat($_price);
            $tax = post_param_tax_code('permission_tax_code');
            if (addon_installed('points')) {
                $price_points = post_param_integer('permission_price_points', null);
            } else {
                $price_points = null;
            }
            $hours = post_param_integer('permission_hours', null);
            $type = post_param_string('permission_type');
            $privilege = post_param_string('permission_privilege');
            $zone = post_param_string('permission_zone');
            $page = post_param_string('privilege_page');
            $module = post_param_string('permission_module');
            $category = post_param_string('permission_category');
            $mail_subject = post_param_string('permission_mail_subject');
            $mail_body = post_param_string('permission_mail_body');

            $map = array(
                'p_enabled' => $enabled,
                'p_price' => $price,
                'p_tax_code' => $tax_code,
                'p_price_points' => $price_points,
                'p_hours' => $hours,
                'p_type' => $type,
                'p_privilege' => $privilege,
                'p_zone' => $zone,
                'p_page' => $page,
                'p_module' => $module,
                'p_category' => $category,
            );
            $map += insert_lang('p_title', $title, 2);
            $map += insert_lang_comcode('p_description', $description, 2);
            $map += insert_lang('p_mail_subject', $mail_subject, 2);
            $map += insert_lang('p_mail_body', $mail_body, 2);
            $GLOBALS['SITE_DB']->query_insert('ecom_prods_permissions', $map);
        }

        log_it('ECOM_PRODUCTS_AMEND_CUSTOM_PERMISSIONS');
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
        $products = array();

        $rows = $GLOBALS['SITE_DB']->query_select('ecom_prods_permissions', array('*'), array('p_enabled' => 1));

        foreach ($rows as $i => $row) {
            $rows[$i]['_title'] = get_translated_text($row['p_title']);
        }
        sort_maps_by($rows, '_title');

        foreach ($rows as $row) {
            $just_row = db_map_restrict($row, array('id', 'p_description'));
            $description = get_translated_tempcode('ecom_prods_permissions', $just_row, 'p_description');
            if (strpos($description->evaluate(), '<img') === false) {
                $image_url = find_theme_image('icons/48x48/menu/adminzone/security/permissions/privileges');
            } else {
                $image_url = '';
            }

            $item_name = $row['p_title'];
            if (strpos($item_name, '(') === false) { // Too simple, wrap the label
                $item_name = do_lang('PERMISSION_PRODUCT', $item_name);
            }

            $products['PERMISSION_' . strval($row['id'])] = automatic_discount_calculation(array(
                'item_name' => $item_name,
                'item_description' => $description,
                'item_image_url' => $image_url,

                'type' => PRODUCT_PURCHASE,
                'type_special_details' => array(),

                'price' => $row['p_price'],
                'currency' => get_option('currency'),
                'price_points' => addon_installed('points') ? $row['p_price_points'] : null,
                'discount_points__num_points' => null,
                'discount_points__price_reduction' => null,

                'tax_code' => $row['p_tax_code'],
                'shipping_cost' => 0.00,
                'needs_shipping_address' => false,
            ));
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

        $permission_product_id = intval(preg_replace('#^PERMISSION\_#', '', $type_code));
        $rows = $GLOBALS['SITE_DB']->query_select('ecom_prods_permissions', array('*'), array('id' => $permission_product_id, 'p_enabled' => 1), '', 1);
        if (!array_key_exists(0, $rows)) {
            return ECOMMERCE_PRODUCT_MISSING;
        }
        $row = $rows[0];

        $map = $this->_get_map($row, $member_id);
        $test = $GLOBALS['SITE_DB']->query_select_value_if_there(filter_naughty_harsh($row['p_type']), 'member_id', $map);
        if ($test !== null) {
            return ECOMMERCE_PRODUCT_ALREADY_HAS;
        }

        return ECOMMERCE_PRODUCT_AVAILABLE;
    }

    /**
     * Get a database map for our permission row.
     *
     * @param  array $row Map row of product
     * @param  MEMBER $member_id The member it is for.
     * @return array Permission map row
     */
    protected function _get_map($row, $member_id)
    {
        $map = array('member_id' => $member_id);
        switch ($row['p_type']) {
            case 'member_privileges':
                $map['privilege'] = $row['p_privilege'];
                $map['the_page'] = $row['p_page'];
                $map['module_the_name'] = $row['p_module'];
                $map['category_name'] = $row['p_category'];
                $map['the_value'] = '1';
                break;
            case 'member_category_access':
                $map['module_the_name'] = $row['p_module'];
                $map['category_name'] = $row['p_category'];
                break;
            case 'member_page_access':
                $map['zone_name'] = $row['p_zone'];
                $map['page_name'] = $row['p_page'];
                break;
            case 'member_zone_access':
                $map['zone_name'] = $row['p_zone'];
                break;
        }
        return $map;
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
        $fields = mixed();
        ecommerce_attach_memo_field_if_needed($fields);

        return array(null, null, null);
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

        $permission_product_id = intval(preg_replace('#^PERMISSION\_#', '', $type_code));

        $rows = $GLOBALS['SITE_DB']->query_select('ecom_prods_permissions', array('*'), array('id' => $permission_product_id, 'p_enabled' => 1), '', 1);
        if (!array_key_exists(0, $rows)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
        }

        $row = $rows[0];

        $p_title = get_translated_text($row['p_title']);

        $member_id = intval($purchase_id);

        $GLOBALS['SITE_DB']->query_insert('ecom_sales', array('date_and_time' => time(), 'member_id' => $member_id, 'details' => $p_title, 'details2' => strval($row['id']), 'txn_id' => $details['TXN_ID']));

        // Actuate
        $map = $this->_get_map($row, $member_id);
        $map['active_until'] = ($row['p_hours'] === null) ? null : (time() + $row['p_hours'] * 60 * 60);
        $GLOBALS['SITE_DB']->query_insert(filter_naughty_harsh($row['p_type']), $map);

        // E-mail member (we don't do a notification as we want to know for sure it will be received; plus avoid bloat in the notification UI)
        require_code('mail');
        $subject_line = get_translated_text($row['p_mail_subject']);
        if ($subject_line != '') {
            $message_raw = get_translated_text($row['p_mail_body']);
            $email = $GLOBALS['FORUM_DRIVER']->get_member_email_address($member_id);
            $to_name = $GLOBALS['FORUM_DRIVER']->get_username($member_id, true);
            mail_wrap($subject_line, $message_raw, array($email), $to_name, '', '', 3, null, false, null, true);
        }

        // Cleanup
        $GLOBALS['SITE_DB']->query('DELETE FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'member_privileges WHERE active_until IS NOT NULL AND active_until<' . strval(time()));
        $GLOBALS['SITE_DB']->query('DELETE FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'member_category_access WHERE active_until IS NOT NULL AND active_until<' . strval(time()));
        $GLOBALS['SITE_DB']->query('DELETE FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'member_page_access WHERE active_until IS NOT NULL AND active_until<' . strval(time()));
        $GLOBALS['SITE_DB']->query('DELETE FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'member_zone_access WHERE active_until IS NOT NULL AND active_until<' . strval(time()));

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
