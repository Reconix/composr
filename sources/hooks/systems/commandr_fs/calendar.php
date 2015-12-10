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
 * @package    calendar
 */

require_code('resource_fs');

/**
 * Hook class.
 */
class Hook_commandr_fs_calendar extends Resource_fs_base
{
    public $folder_resource_type = 'calendar_type';
    public $file_resource_type = 'event';

    /**
     * Standard commandr_fs function for seeing how many resources are. Useful for determining whether to do a full rebuild.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @return integer How many resources there are
     */
    public function get_resources_count($resource_type)
    {
        switch ($resource_type) {
            case 'event':
                return $GLOBALS['SITE_DB']->query_select_value('calendar_events', 'COUNT(*)');

            case 'calendar_type':
                return $GLOBALS['SITE_DB']->query_select_value('calendar_types', 'COUNT(*)');
        }
        return 0;
    }

    /**
     * Standard commandr_fs function for searching for a resource by label.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @param  LONG_TEXT $label The resource label
     * @return array A list of resource IDs
     */
    public function find_resource_by_label($resource_type, $label)
    {
        switch ($resource_type) {
            case 'event':
                $_ret = $GLOBALS['SITE_DB']->query_select('calendar_events', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('e_title') => $label));
                $ret = array();
                foreach ($_ret as $r) {
                    $ret[] = strval($r['id']);
                }
                return $ret;

            case 'calendar_type':
                $_ret = $GLOBALS['SITE_DB']->query_select('calendar_types', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('t_title') => $label));
                $ret = array();
                foreach ($_ret as $r) {
                    $ret[] = strval($r['id']);
                }
                return $ret;
        }
        return array();
    }

    /**
     * Standard commandr_fs date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
     *
     * @param  array $row Resource row (not full, but does contain the ID)
     * @return ?TIME The edit date or add date, whichever is higher (null: could not find one)
     */
    protected function _get_folder_edit_date($row)
    {
        $query = 'SELECT MAX(date_and_time) FROM ' . get_table_prefix() . 'adminlogs WHERE ' . db_string_equal_to('param_a', strval($row['id'])) . ' AND  (' . db_string_equal_to('the_type', 'ADD_EVENT_TYPE') . ' OR ' . db_string_equal_to('the_type', 'EDIT_EVENT_TYPE') . ')';
        return $GLOBALS['SITE_DB']->query_value_if_there($query);
    }

    /**
     * Standard commandr_fs add function for resource-fs hooks. Adds some resource with the given label and properties.
     *
     * @param  LONG_TEXT $filename Filename OR Resource label
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error)
     */
    public function folder_add($filename, $path, $properties)
    {
        if ($path != '') {
            return false; // Only one depth allowed for this resource type
        }

        list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties);

        require_code('calendar2');

        $logo = $this->_default_property_str($properties, 'logo');
        $external_feed = $this->_default_property_str($properties, 'external_feed');
        $id = add_event_type($label, $logo, $external_feed);
        return strval($id);
    }

    /**
     * Standard commandr_fs load function for resource-fs hooks. Finds the properties for some resource.
     *
     * @param  SHORT_TEXT $filename Filename
     * @param  string $path The path (blank: root / not applicable). It may be a wildcarded path, as the path is used for content-type identification only. Filenames are globally unique across a hook; you can calculate the path using ->search.
     * @return ~array Details of the resource (false: error)
     */
    public function folder_load($filename, $path)
    {
        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename);

        $rows = $GLOBALS['SITE_DB']->query_select('calendar_types', array('*'), array('id' => intval($resource_id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        return array(
            'label' => $row['t_title'],
            'logo' => $row['t_logo'],
            'external_feed' => $row['t_external_feed'],
        );
    }

    /**
     * Standard commandr_fs edit function for resource-fs hooks. Edits the resource to the given properties.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function folder_edit($filename, $path, $properties)
    {
        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename);

        require_code('calendar2');

        $label = $this->_default_property_str($properties, 'label');
        $logo = $this->_default_property_str($properties, 'logo');
        $external_feed = $this->_default_property_str($properties, 'external_feed');

        edit_event_type(intval($resource_id), $label, $logo, $external_feed);

        return $resource_id;
    }

    /**
     * Standard commandr_fs delete function for resource-fs hooks. Deletes the resource.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @return boolean Success status
     */
    public function folder_delete($filename, $path)
    {
        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename);

        require_code('calendar2');
        delete_event_type(intval($resource_id));

        return true;
    }

    /**
     * Standard commandr_fs date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
     *
     * @param  array $row Resource row (not full, but does contain the ID)
     * @return ?TIME The edit date or add date, whichever is higher (null: could not find one)
     */
    protected function _get_file_edit_date($row)
    {
        $query = 'SELECT MAX(date_and_time) FROM ' . get_table_prefix() . 'adminlogs WHERE ' . db_string_equal_to('param_a', strval($row['id'])) . ' AND  (' . db_string_equal_to('the_type', 'ADD_CALENDAR_EVENT') . ' OR ' . db_string_equal_to('the_type', 'EDIT_CALENDAR_EVENT') . ')';
        return $GLOBALS['SITE_DB']->query_value_if_there($query);
    }

    /**
     * Standard commandr_fs add function for resource-fs hooks. Adds some resource with the given label and properties.
     *
     * @param  LONG_TEXT $filename Filename OR Resource label
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function file_add($filename, $path, $properties)
    {
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path);
        list($properties, $label) = $this->_file_magic_filter($filename, $path, $properties);

        if (is_null($category)) {
            return false; // Folder not found
        }

        require_code('calendar2');

        $type = $this->_integer_category($category);
        $recurrence = $this->_default_property_str($properties, 'recurrence');
        $recurrences = $this->_default_property_int_null($properties, 'recurrences');
        $seg_recurrences = $this->_default_property_int($properties, 'seg_recurrences');
        $content = $this->_default_property_str($properties, 'description');
        $priority = $this->_default_property_int_null($properties, 'priority');
        if ($priority === null) {
            $priority = 3;
        }
        $start_year = $this->_default_property_int_null($properties, 'start_year');
        if ($start_year === null) {
            $start_year = intval(date('Y'));
        }
        $start_month = $this->_default_property_int_null($properties, 'start_month');
        if ($start_month === null) {
            $start_month = intval(date('m'));
        }
        $start_day = $this->_default_property_int_null($properties, 'start_day');
        if ($start_day === null) {
            $start_day = intval(date('d'));
        }
        $start_monthly_spec_type = $this->_default_property_str($properties, 'start_monthly_spec_type');
        if ($start_monthly_spec_type == '') {
            $start_monthly_spec_type = 'day_of_month';
        }
        $start_hour = $this->_default_property_int_null($properties, 'start_hour');
        $start_minute = $this->_default_property_int_null($properties, 'start_minute');
        $end_year = $this->_default_property_int_null($properties, 'end_year');
        $end_month = $this->_default_property_int_null($properties, 'end_month');
        $end_day = $this->_default_property_int_null($properties, 'end_day');
        $end_monthly_spec_type = $this->_default_property_str($properties, 'end_monthly_spec_type');
        if ($end_monthly_spec_type == '') {
            $end_monthly_spec_type = 'day_of_month';
        }
        $end_hour = $this->_default_property_int_null($properties, 'end_hour');
        $end_minute = $this->_default_property_int_null($properties, 'end_minute');
        $timezone = $this->_default_property_str($properties, 'timezone');
        $do_timezone_conv = $this->_default_property_int($properties, 'do_timezone_conv');
        $validated = $this->_default_property_int_null($properties, 'validated');
        if (is_null($validated)) {
            $validated = 1;
        }
        $allow_rating = $this->_default_property_int_modeavg($properties, 'allow_rating', 'calendar_events', 1);
        $allow_comments = $this->_default_property_int_modeavg($properties, 'allow_comments', 'calendar_events', 1);
        $allow_trackbacks = $this->_default_property_int_modeavg($properties, 'allow_trackbacks', 'calendar_events', 1);
        $notes = $this->_default_property_str($properties, 'notes');
        $member_calendar = $this->_default_property_member_null($properties, 'member_calendar');
        $submitter = $this->_default_property_member($properties, 'submitter');
        $views = $this->_default_property_int($properties, 'views');
        $add_time = $this->_default_property_time($properties, 'add_date');
        $edit_time = $this->_default_property_time_null($properties, 'edit_date');
        $meta_keywords = $this->_default_property_str($properties, 'meta_keywords');
        $meta_description = $this->_default_property_str($properties, 'meta_description');
        $regions = empty($properties['regions']) ? array() : $properties['regions'];

        $id = add_calendar_event($type, $recurrence, $recurrences, $seg_recurrences, $label, $content, $priority, $start_year, $start_month, $start_day, $start_monthly_spec_type, $start_hour, $start_minute, $end_year, $end_month, $end_day, $end_monthly_spec_type, $end_hour, $end_minute, $timezone, $do_timezone_conv, $member_calendar, $validated, $allow_rating, $allow_comments, $allow_trackbacks, $notes, $submitter, $views, $add_time, $edit_time, null, $meta_keywords, $meta_description, $regions);
        return strval($id);
    }

    /**
     * Standard commandr_fs load function for resource-fs hooks. Finds the properties for some resource.
     *
     * @param  SHORT_TEXT $filename Filename
     * @param  string $path The path (blank: root / not applicable). It may be a wildcarded path, as the path is used for content-type identification only. Filenames are globally unique across a hook; you can calculate the path using ->search.
     * @return ~array Details of the resource (false: error)
     */
    public function file_load($filename, $path)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);

        $rows = $GLOBALS['SITE_DB']->query_select('calendar_events', array('*'), array('id' => intval($resource_id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        list($meta_keywords, $meta_description) = seo_meta_get_for('events', strval($row['id']));

        return array(
            'label' => $row['e_title'],
            'description' => $row['e_content'],
            'start_year' => $row['e_start_year'],
            'start_month' => $row['e_start_month'],
            'start_day' => $row['e_start_day'],
            'start_monthly_spec_type' => $row['e_start_monthly_spec_type'],
            'start_hour' => $row['e_start_hour'],
            'start_minute' => $row['e_start_minute'],
            'end_year' => $row['e_end_year'],
            'end_month' => $row['e_end_month'],
            'end_day' => $row['e_end_day'],
            'end_monthly_spec_type' => $row['e_end_monthly_spec_type'],
            'end_hour' => $row['e_end_hour'],
            'end_minute' => $row['e_end_minute'],
            'timezone' => $row['e_timezone'],
            'do_timezone_conv' => $row['e_do_timezone_conv'],
            'recurrence' => $row['e_recurrence'],
            'recurrences' => $row['e_recurrences'],
            'seg_recurrences' => $row['e_seg_recurrences'],
            'priority' => $row['e_priority'],
            'validated' => $row['validated'],
            'allow_rating' => $row['allow_rating'],
            'allow_comments' => $row['allow_comments'],
            'allow_trackbacks' => $row['allow_trackbacks'],
            'notes' => $row['notes'],
            'views' => $row['e_views'],
            'meta_keywords' => $meta_keywords,
            'meta_description' => $meta_description,
            'submitter' => remap_resource_id_as_portable('member', $row['e_submitter']),
            'member_calendar' => remap_resource_id_as_portable('member', $row['e_member_calendar']),
            'add_date' => remap_time_as_portable($row['e_add_date']),
            'edit_date' => remap_time_as_portable($row['e_edit_date']),
            'regions' => collapse_1d_complexity('region', $GLOBALS['SITE_DB']->query_select('content_regions', array('region'), array('content_type' => 'event', 'content_id' => strval($row['id'])))),
        );
    }

    /**
     * Standard commandr_fs edit function for resource-fs hooks. Edits the resource to the given properties.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function file_edit($filename, $path, $properties)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path);
        list($properties,) = $this->_file_magic_filter($filename, $path, $properties);

        if (is_null($category)) {
            return false; // Folder not found
        }

        require_code('calendar2');

        $label = $this->_default_property_str($properties, 'label');
        $type = $this->_integer_category($category);
        $recurrence = $this->_default_property_str($properties, 'recurrence');
        $recurrences = $this->_default_property_int_null($properties, 'recurrences');
        $seg_recurrences = $this->_default_property_int($properties, 'seg_recurrences');
        $content = $this->_default_property_str($properties, 'description');
        $priority = $this->_default_property_int_null($properties, 'priority');
        if ($priority === null) {
            $priority = 3;
        }
        $start_year = $this->_default_property_int_null($properties, 'start_year');
        if ($start_year === null) {
            $start_year = intval(date('Y'));
        }
        $start_month = $this->_default_property_int_null($properties, 'start_month');
        if ($start_month === null) {
            $start_month = intval(date('m'));
        }
        $start_day = $this->_default_property_int_null($properties, 'start_day');
        if ($start_day === null) {
            $start_day = intval(date('d'));
        }
        $start_monthly_spec_type = $this->_default_property_str($properties, 'start_monthly_spec_type');
        if ($start_monthly_spec_type == '') {
            $start_monthly_spec_type = 'day_of_month';
        }
        $start_hour = $this->_default_property_int_null($properties, 'start_hour');
        $start_minute = $this->_default_property_int_null($properties, 'start_minute');
        $end_year = $this->_default_property_int_null($properties, 'end_year');
        $end_month = $this->_default_property_int_null($properties, 'end_month');
        $end_day = $this->_default_property_int_null($properties, 'end_day');
        $end_monthly_spec_type = $this->_default_property_str($properties, 'end_monthly_spec_type');
        if ($end_monthly_spec_type == '') {
            $end_monthly_spec_type = 'day_of_month';
        }
        $end_hour = $this->_default_property_int_null($properties, 'end_hour');
        $end_minute = $this->_default_property_int_null($properties, 'end_minute');
        $timezone = $this->_default_property_str($properties, 'timezone');
        $do_timezone_conv = $this->_default_property_int($properties, 'do_timezone_conv');
        $validated = $this->_default_property_int_null($properties, 'validated');
        if (is_null($validated)) {
            $validated = 1;
        }
        $allow_rating = $this->_default_property_int_modeavg($properties, 'allow_rating', 'calendar_events', 1);
        $allow_comments = $this->_default_property_int_modeavg($properties, 'allow_comments', 'calendar_events', 1);
        $allow_trackbacks = $this->_default_property_int_modeavg($properties, 'allow_trackbacks', 'calendar_events', 1);
        $notes = $this->_default_property_str($properties, 'notes');
        $member_calendar = $this->_default_property_member_null($properties, 'member_calendar');
        $submitter = $this->_default_property_member($properties, 'submitter');
        $views = $this->_default_property_int($properties, 'views');
        $add_time = $this->_default_property_time($properties, 'add_date');
        $edit_time = $this->_default_property_time($properties, 'edit_date');
        $meta_keywords = $this->_default_property_str($properties, 'meta_keywords');
        $meta_description = $this->_default_property_str($properties, 'meta_description');
        $regions = empty($properties['regions']) ? array() : $properties['regions'];

        edit_calendar_event(intval($resource_id), $type, $recurrence, $recurrences, $seg_recurrences, $label, $content, $priority, $start_year, $start_month, $start_day, $start_monthly_spec_type, $start_hour, $start_minute, $end_year, $end_month, $end_day, $end_monthly_spec_type, $end_hour, $end_minute, $timezone, $do_timezone_conv, $member_calendar, $meta_keywords, $meta_description, $validated, $allow_rating, $allow_comments, $allow_trackbacks, $notes, $edit_time, $add_time, $views, $submitter, $regions, true);

        return $resource_id;
    }

    /**
     * Standard commandr_fs delete function for resource-fs hooks. Deletes the resource.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @return boolean Success status
     */
    public function file_delete($filename, $path)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);

        require_code('calendar2');
        delete_calendar_event(intval($resource_id));

        return true;
    }
}
