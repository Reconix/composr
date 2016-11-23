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

/*EXTRA FUNCTIONS: get_included_files*/

/**
 * Edit a language string direct from something saved into the code.
 *
 * @param  ID_TEXT $codename The language string ID
 * @param  ?LANGUAGE_NAME $lang The language to use (null: users language)
 */
function inline_language_editing($codename, $lang)
{
    global $LANGS_REQUESTED, $LANGUAGE_STRINGS_CACHE;

    // Find loaded file with smallest levenstein distance to current page
    $best = mixed();
    $best_for = null;
    foreach (array_keys($LANGS_REQUESTED) as $possible) {
        $dist = levenshtein(get_page_name(), $possible);
        if (($best === null) || ($best > $dist)) {
            $best = $dist;
            $best_for = $possible;
        }
    }
    $save_path = get_file_base() . '/lang/' . fallback_lang() . '/' . $best_for . '.ini';
    if (!is_file($save_path)) {
        $save_path = get_file_base() . '/lang_custom/' . fallback_lang() . '/' . $best_for . '.ini';
    }
    // Tack language strings onto this file
    list($codename, $value) = explode('=', $codename, 2);
    $myfile = fopen($save_path, 'ab');
    fwrite($myfile, "\n" . $codename . '=' . $value);
    fclose($myfile);
    // Fake-load the string
    $LANGUAGE_STRINGS_CACHE[$lang][$codename] = $value;
    // Go through all required files, doing a string replace if needed
    $included_files = get_included_files();
    foreach ($included_files as $inc) {
        $orig_contents = file_get_contents($inc);
        $contents = str_replace("'" . $codename . '=' . $value . "'", "'" . $codename . "'", $orig_contents);
        if ($orig_contents != $contents) {
            $myfile = fopen($inc, GOOGLE_APPENGINE ? 'wb' : 'ab');
            @flock($myfile, LOCK_EX);
            if (!GOOGLE_APPENGINE) {
                ftruncate($myfile, 0);
            }
            fwrite($myfile, $contents);
            @flock($myfile, LOCK_UN);
            fclose($myfile);
        }
    }
}

/**
 * Get a list of languages files for the given language. ONLY those that are overridden.
 *
 * @param  ?LANGUAGE_NAME $lang The language (null: uses the current language)
 * @return array The language files, a map between codename to directory
 */
function get_lang_files($lang = null)
{
    require_code('files');

    if ($lang === null) {
        $lang = get_site_default_lang();
    }

    $_dir = @opendir(get_file_base() . '/lang/' . $lang);
    $_lang_files = array();
    if ($_dir !== false) {
        while (false !== ($file = readdir($_dir))) {
            if (($file[0] != '.') && (substr($file, -4) == '.ini')/* && (!should_ignore_file(get_file_base().'/lang/'.$lang.'/'.$file,0,0))*/) {
                $file = substr($file, 0, strlen($file) - 4);
                $_lang_files[$file] = 'lang';
            }
        }
        closedir($_dir);
    }
    $_dir = @opendir(get_custom_file_base() . '/lang_custom/' . $lang);
    if ($_dir !== false) {
        while (false !== ($file = readdir($_dir))) {
            if (($file[0] != '.') && (substr($file, -4) == '.ini')/* && (!should_ignore_file(get_custom_file_base().'/lang_custom/'.$lang.'/'.$file,0,0))*/) {
                $file = substr($file, 0, strlen($file) - 4);
                $_lang_files[$file] = 'lang_custom';
            }
        }
        closedir($_dir);
    }
    if (get_file_base() != get_custom_file_base()) {
        $_dir = @opendir(get_file_base() . '/lang_custom/' . $lang);
        if ($_dir !== false) {
            while (false !== ($file = readdir($_dir))) {
                if (($file != '.') && ($file != '..') && (substr($file, -4) == '.ini') && (!should_ignore_file(get_file_base() . '/lang_custom/' . $lang . '/' . $file, 0, 0))) {
                    $file = substr($file, 0, strlen($file) - 4);
                    $_lang_files[$file] = 'lang_custom';
                }
            }
            closedir($_dir);
        }
    }

    return $_lang_files;
}

/**
 * Search the database to find human-readable names for language string IDs.
 *
 * @param  array $ids The language string IDs (array of AUTO_LINK)
 * @return array Human readable names (List of string against same IDs in input array or null for orphan strings)
 */
function find_lang_content_names($ids)
{
    static $langidfields = null;
    if ($langidfields === null) {
        $query = 'SELECT m_name,m_table,m_type FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'db_meta';
        $all_fields = $GLOBALS['SITE_DB']->query($query);

        $langidfields = array();
        foreach ($all_fields as $f) {
            if (strpos(substr($f['m_type'], -6), '_TRANS') !== false) {
                $langidfields[] = array('m_name' => $f['m_name'], 'm_table' => $f['m_table'], 'key' => '');
            }
        }
        foreach ($langidfields as $i => $l) {
            foreach ($all_fields as $f) {
                if (($l['m_table'] == $f['m_table']) && (substr($f['m_type'], 0, 1) == '*') && ($l['key'] == '')) {
                    $langidfields[$i]['key'] = $f['m_name'];
                }
            }
        }
    }

    $ret = array();

    foreach ($langidfields as $field) {
        $db = $GLOBALS[((substr($field['m_table'], 0, 2) == 'f_') ? 'FORUM_DB' : 'SITE_DB')];
        if ($db === null) {
            continue; // None forum driver
        }
        $or_list = '';
        foreach ($ids as $id) {
            if (!isset($ret[$id])) { // Try and lookup anything not found yet
                if ($or_list != '') {
                    $or_list .= ' OR ';
                }
                $or_list .= ($field['m_table'] == 'config') ? db_string_equal_to($field['m_name'], strval($id)) : ($field['m_name'] . '=' . strval($id));
            }
        }
        if ($or_list != '') {
            $test = list_to_map($field['m_name'], $db->query('SELECT * FROM ' . $db->get_table_prefix() . $field['m_table'] . ' WHERE ' . $or_list));
            foreach ($ids as $id) {
                if (array_key_exists($id, $test)) {
                    $cma_hooks = find_all_hooks('systems', 'content_meta_aware');
                    foreach (array_keys($cma_hooks) as $hook) {
                        require_code('content');
                        $ob = get_content_object($hook);
                        if ($ob === null) {
                            continue;
                        }

                        $info = $ob->info();
                        if ($info === null) {
                            continue;
                        }

                        if ($info['table'] == $field['m_table']) {
                            if ($info['title_field_dereference']) {
                                $ret[$id] = $field['m_table'] . ' \ ' . get_translated_text($test[$id][$info['title_field']]) . ' \ ' . $field['m_name'];
                            } else {
                                if (strpos($info['title_field'], 'CALL:') !== false) {
                                    $ret[$id] = call_user_func(trim(substr($info['title_field'], 5)), array('id' => $test[$id][$info['id_field']]), false);
                                } else {
                                    $ret[$id] = $field['m_table'] . ' \ ' . (is_integer($test[$id][$info['title_field']]) ? strval($test[$id][$info['title_field']]) : $test[$id][$info['title_field']]) . ' \ ' . $field['m_name'];
                                }
                            }

                            continue 2;
                        }
                    }

                    $ret[$id] = $field['m_table'] . ' \ ' . (is_integer($test[$id][$field['key']]) ? strval($test[$id][$field['key']]) : $test[$id][$field['key']]) . ' \ ' . $field['m_name'];
                } else {
                    if (!array_key_exists($id, $ret)) {
                        $ret[$id] = null;
                    }
                }
            }
        }
    }

    return $ret;
}

/**
 * Get a nice formatted XHTML listed language file selector for the given language.
 *
 * @param  ?LANGUAGE_NAME $lang The language (null: uses the current language)
 * @return Tempcode The language file selector
 */
function create_selection_list_lang_files($lang = null)
{
    $_lang_files = get_lang_files(($lang === null) ? get_site_default_lang() : $lang);

    ksort($_lang_files);

    require_lang('lang');
    require_code('lang_compile');

    $lang_files = new Tempcode();
    foreach (array_keys($_lang_files) as $lang_file) {
        if ($lang !== null) {
            $base_map = get_lang_file_map(fallback_lang(), $lang_file, true);
            $criticise_map = get_lang_file_map($lang, $lang_file);

            $num_translated = 0;
            $num_english = count($base_map);

            foreach ($base_map as $key => $val) {
                if (array_key_exists($key, $criticise_map)) {
                    $num_translated++;
                }
            }

            $lang_files->attach(form_input_list_entry($lang_file, false, do_lang_tempcode('TRANSLATION_PROGRESS', escape_html($lang_file), escape_html(integer_format($num_translated)), escape_html(integer_format($num_english)))));
        } else {
            $lang_files->attach(form_input_list_entry($lang_file, false, $lang_file));
        }
    }

    return $lang_files;
}

/**
 * Get the full name of a language. e.g. 'EN' would become 'English'
 *
 * @param  LANGUAGE_NAME $code The language
 * @return string The full name of the language
 */
function lookup_language_full_name($code)
{
    global $LANGS_MAP_CACHE;

    if ($code == 'EN') {
        return 'English'; // Optimisation
    }

    if ($LANGS_MAP_CACHE === null) {
        $LANGS_MAP_CACHE = persistent_cache_get('LANGS_MAP_CACHE');
    }
    if ($LANGS_MAP_CACHE === null) {
        require_code('files');
        $map_file_a = get_file_base() . '/lang/langs.ini';
        $map_file_b = get_custom_file_base() . '/lang_custom/langs.ini';
        if (!is_file($map_file_b)) {
            $map_file_b = $map_file_a;
        }
        $LANGS_MAP_CACHE = better_parse_ini_file($map_file_b);

        persistent_cache_set('LANGS_MAP_CACHE', $LANGS_MAP_CACHE);
    }
    return isset($LANGS_MAP_CACHE[$code]) ? $LANGS_MAP_CACHE[$code] : $code;
}
