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
 * @package    news
 */

/**
 * Hook class.
 */
class Hook_content_meta_aware_news_category
{
    /**
     * Get content type details. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
     *
     * @param  ?ID_TEXT $zone The zone to link through to (null: autodetect).
     * @return ?array Map of award content-type info (null: disabled).
     */
    public function info($zone = null)
    {
        return array(
            'support_custom_fields' => false,

            'content_type_label' => 'news:NEWS_CATEGORY',

            'connection' => $GLOBALS['SITE_DB'],
            'table' => 'news_categories',
            'id_field' => 'id',
            'id_field_numeric' => true,
            'parent_category_field' => null,
            'parent_category_meta_aware_type' => 'news_category',
            'is_category' => true,
            'is_entry' => false,
            'category_type' => 'news', // For category permissions
            'parent_spec__table_name' => null,
            'parent_spec__parent_name' => null,
            'parent_spec__field_name' => null,
            'category_field' => null, // For category permissions
            'category_is_string' => false,

            'title_field' => 'nc_title',
            'title_field_dereference' => true,
            'description_field' => null,
            'thumb_field' => 'nc_img',
            'thumb_field_is_theme_image' => true,

            'view_page_link_pattern' => '_SEARCH:news:browse:_WILD',
            'edit_page_link_pattern' => '_SEARCH:cms_news:_edit_category:_WILD',
            'view_category_page_link_pattern' => '_SEARCH:news:browse:_WILD',
            'add_url' => (function_exists('has_submit_permission') && has_submit_permission('mid', get_member(), get_ip_address(), 'cms_news')) ? (get_module_zone('cms_news') . ':cms_news:add') : null,
            'archive_url' => ((!is_null($zone)) ? $zone : get_module_zone('news')) . ':news',

            'support_url_monikers' => true,

            'views_field' => null,
            'order_field' => null,
            'submitter_field' => null,
            'author_field' => null,
            'add_time_field' => null,
            'edit_time_field' => null,
            'date_field' => null,
            'validated_field' => null,

            'seo_type_code' => 'news_category',

            'feedback_type_code' => null,

            'permissions_type_code' => 'news', // NULL if has no permissions

            'search_hook' => null,
            'rss_hook' => null,
            'attachment_hook' => null,
            'unvalidated_hook' => null,
            'notification_hook' => null,
            'sitemap_hook' => 'news_category',

            'addon_name' => 'news',

            'cms_page' => 'cms_news',
            'module' => 'news',

            'commandr_filesystem_hook' => 'news',
            'commandr_filesystem__is_folder' => true,

            'support_revisions' => false,

            'support_privacy' => false,

            'support_content_reviews' => true,

            'actionlog_regexp' => '\w+_NEWS_CATEGORY',
        );
    }

    /**
     * Run function for content hooks. Renders a content box for an award/randomisation.
     *
     * @param  array $row The database row for the content
     * @param  ID_TEXT $zone The zone to display in
     * @param  boolean $give_context Whether to include context (i.e. say WHAT this is, not just show the actual content)
     * @param  boolean $include_breadcrumbs Whether to include breadcrumbs (if there are any)
     * @param  ?ID_TEXT $root Virtual root to use (null: none)
     * @param  boolean $attach_to_url_filter Whether to copy through any filter parameters in the URL, under the basis that they are associated with what this box is browsing
     * @param  ID_TEXT $guid Overridden GUID to send to templates (blank: none)
     * @return Tempcode Results
     */
    public function run($row, $zone, $give_context = true, $include_breadcrumbs = true, $root = null, $attach_to_url_filter = false, $guid = '')
    {
        require_code('news');

        return render_news_category_box($row, $zone, $give_context, $attach_to_url_filter, null, $guid);
    }
}
