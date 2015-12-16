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
 * @package    cns_forum
 */

/**
 * Hook class.
 */
class Hook_content_meta_aware_forum
{
    /**
     * Get content type details. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
     *
     * @param  ?ID_TEXT $zone The zone to link through to (null: autodetect).
     * @return ?array Map of award content-type info (null: disabled).
     */
    public function info($zone = null)
    {
        if (get_forum_type() != 'cns') {
            return null;
        }

        return array(
            'supports_custom_fields' => true,

            'content_type_label' => 'cns:FORUM',

            'connection' => $GLOBALS['FORUM_DB'],
            'table' => 'f_forums',
            'id_field' => 'id',
            'id_field_numeric' => true,
            'parent_category_field' => 'f_parent_forum',
            'parent_category_meta_aware_type' => 'forum',
            'is_category' => true,
            'is_entry' => false,
            'category_field' => 'id', // For category permissions
            'category_type' => 'forums', // For category permissions
            'parent_spec__table_name' => 'f_forums',
            'parent_spec__parent_name' => 'f_parent_forum',
            'parent_spec__field_name' => 'id',
            'category_is_string' => false,

            'title_field' => 'f_name',
            'title_field_dereference' => false,
            'description_field' => 'f_description',
            'thumb_field' => null,

            'view_page_link_pattern' => '_SEARCH:forumview:browse:_WILD',
            'edit_page_link_pattern' => '_SEARCH:admin_cns_forums:_edit_category:_WILD',
            'view_category_page_link_pattern' => '_SEARCH:forumview:browse:_WILD',
            'add_url' => '',
            'archive_url' => ((!is_null($zone)) ? $zone : get_module_zone('forumview')) . ':forumview',

            'support_url_monikers' => true,

            'views_field' => null,
            'order_field' => 'f_position',
            'submitter_field' => null,
            'add_time_field' => null,
            'edit_time_field' => null,
            'date_field' => null,
            'validated_field' => null,

            'seo_type_code' => null,

            'feedback_type_code' => null,

            'permissions_type_code' => 'forums', // NULL if has no permissions

            'search_hook' => null,

            'addon_name' => 'cns_forum',

            'cms_page' => 'topics',
            'module' => 'forumview',

            'commandr_filesystem_hook' => 'forums',
            'commandr_filesystem__is_folder' => true,

            'support_revisions' => false,

            'rss_hook' => null,

            'actionlog_regexp' => '\w+_FORUM',
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
        require_code('cns_forums');

        return render_forum_box($row, $zone, $give_context, $include_breadcrumbs, is_null($root) ? null : intval($root), $guid);
    }
}
