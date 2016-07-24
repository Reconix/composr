<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/*EXTRA FUNCTIONS: curl_.**/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Script to make a nice textual image, vertical writing.
 *
 * @ignore
 */
function gd_text_script()
{
    if (!function_exists('imagefontwidth')) {
        return;
    }

    $text = get_param_string('text', false, true);
    if (get_magic_quotes_gpc()) {
        $text = stripslashes($text);
    }

    $direction = array_key_exists('direction', $_GET) ? $_GET['direction'] : 'vertical';

    $font_size = array_key_exists('size', $_GET) ? intval($_GET['size']) : 8;

    $default_font = 'Courier New Bold Italic'; // Support ideal font if it is there (we cannot distribute)
    if (!is_file(get_file_base() . '/data_custom/fonts/' . filter_naughty($default_font) . '.ttf')) {
        $default_font = 'FreeMonoBoldOblique'; // Fallback to distributed
    }
    $_font = get_param_string('font', $default_font);
    $font = get_file_base() . '/data_custom/fonts/' . filter_naughty($_font) . '.ttf';
    if (!is_file($font)) {
        $font = get_file_base() . '/data/fonts/' . filter_naughty($_font) . '.ttf';
    }

    if ((!function_exists('imagettftext')) || (!array_key_exists('FreeType Support', gd_info())) || (@imagettfbbox(26.0, 0.0, get_file_base() . '/data/fonts/Vera.ttf', 'test') === false) || (strlen($text) == 0)) {
        switch ($font_size) {
            case 1:
            case 2:
                $pfont = 1;
                break;

            case 3:
            case 4:
                $pfont = 2;
                break;

            case 5:
            case 6:
                $pfont = 3;
                break;

            case 7:
            case 8:
                $pfont = 4;
                break;

            default:
                $pfont = 5;
        }
        $baseline_offset = 0;

        if ($direction == 'horizontal') {
            $width = intval(imagefontwidth($pfont) * strlen($text) * 1.05);
            $height = imagefontheight($pfont);
        } else {
            $height = intval(imagefontwidth($pfont) * strlen($text) * 1.05);
            $width = imagefontheight($pfont);
        }
    } else {
        $scale = 4;

        if ($direction == 'horizontal') {
            list(, , $width, , , , , $height) = imagettfbbox(floatval($font_size * $scale), 0.0, $font, $text);
            $baseline_offset = 2 * $scale * intval(ceil(floatval($font_size) / 8.0));
            $height = abs($height);
            $width += $font_size * $scale * 2; // This is just due to inaccuracy in imagettfbbox, possibly due to italics not being computed correctly
            $height += $baseline_offset;

            list(, , $real_width, , , , , $real_height) = imagettfbbox(floatval($font_size), 0.0, $font, $text);
            $real_height = abs($real_height);
            $real_width += $font_size * 2;
            $real_height += $baseline_offset / $scale;
        } else {
            list(, , $height, , , , , $width) = imagettfbbox(floatval($font_size * $scale), 0.0, $font, $text);
            $baseline_offset = 2 * $scale * intval(ceil(floatval($font_size) / 8.0));
            $width = abs($width);
            $width += $baseline_offset;
            $height += $font_size * $scale * 2; // This is just due to inaccuracy in imagettfbbox, possibly due to italics not being computed correctly

            list(, , $real_height, , , , , $real_width) = imagettfbbox(floatval($font_size), 0.0, $font, $text);
            $real_width = abs($real_width);
            $real_width += $baseline_offset / $scale;
            $real_height += $font_size * 2;
        }
    }
    if ($width == 0) {
        $width = 1;
    }
    if ($height == 0) {
        $height = 1;
    }
    $trans_color = array_key_exists('trans_color', $_GET) ? $_GET['trans_color'] : 'FF00FF';
    $img = imagecreatetruecolor($width, $height + $baseline_offset);
    imagealphablending($img, false);
    $fg_color = array_key_exists('fg_color', $_GET) ? $_GET['fg_color'] : '000000';
    if (substr($fg_color, 0, 5) == 'seed-') {
        $theme = substr($fg_color, 5);

        if (addon_installed('themewizard')) {
            require_code('themewizard');
            $fg_color = find_theme_seed($theme);
        } else {
            $ini_path = (($theme == 'default') ? get_file_base() : get_custom_file_base()) . '/themes/' . filter_naughty($theme) . '/theme.ini';
            if (is_file($ini_path)) {
                require_code('files');
                $map = better_parse_ini_file($ini_path);
            } else {
                $map = array();
            }
            $fg_color = isset($map['seed']) ? $map['seed'] : '000000';
        }
    }
    $color = imagecolorallocate($img, hexdec(substr($fg_color, 0, 2)), hexdec(substr($fg_color, 2, 2)), hexdec(substr($fg_color, 4, 2)));
    if ((!function_exists('imagettftext')) || (!array_key_exists('FreeType Support', gd_info())) || (@imagettfbbox(26.0, 0.0, get_file_base() . '/data/fonts/Vera.ttf', 'test') === false) || (strlen($text) == 0)) {
        $trans = imagecolorallocate($img, hexdec(substr($trans_color, 0, 2)), hexdec(substr($trans_color, 2, 2)), hexdec(substr($trans_color, 4, 2)));
        imagefill($img, 0, 0, $trans);
        imagecolortransparent($img, $trans);
        if ($direction == 'horizontal') {
            imagestring($img, $pfont, 0, intval($height * 0.02), $text, $color);
        } else {
            imagestringup($img, $pfont, 0, $height - 1 - intval($height * 0.02), $text, $color);
        }
    } else {
        if (function_exists('imagecolorallocatealpha')) {
            $trans = imagecolorallocatealpha($img, hexdec(substr($trans_color, 0, 2)), hexdec(substr($trans_color, 2, 2)), hexdec(substr($trans_color, 4, 2)), 127);
        } else {
            $trans = imagecolorallocate($img, hexdec(substr($trans_color, 0, 2)), hexdec(substr($trans_color, 2, 2)), hexdec(substr($trans_color, 4, 2)));
        }
        imagefilledrectangle($img, 0, 0, $width, $height, $trans);
        require_code('character_sets');
        $text = utf8tohtml(convert_to_internal_encoding($text, strtolower(get_param_string('charset', get_charset())), 'utf-8'));
        if (strpos($text, '&#') === false) {
            $previous = mixed();
            $nxpos = 0;
            for ($i = 0; $i < strlen($text); $i++) {
                if ($previous !== null) { // check for existing previous character
                    list(, , $rx1, , $rx2) = imagettfbbox(floatval($font_size * $scale), 0.0, $font, $previous);
                    $nxpos += max($rx1, $rx2) + 3;
                }
                if ($direction == 'horizontal') {
                    imagettftext($img, floatval($font_size * $scale), 0.0, $nxpos, $height - $baseline_offset, $color, $font, $text[$i]);
                } else {
                    imagettftext($img, floatval($font_size * $scale), 270.0, $baseline_offset, $nxpos, $color, $font, $text[$i]);
                }
                $previous = $text[$i];
            }
        } else {
            if ($direction == 'horizontal') {
                imagettftext($img, floatval($font_size * $scale), 0.0, 0, $height - 4, $color, $font, $text);
            } else {
                imagettftext($img, floatval($font_size * $scale), 270.0, 4, 0, $color, $font, $text);
            }
        }
        $dest_img = imagecreatetruecolor($real_width + intval(ceil(floatval($baseline_offset) / floatval($scale))), $real_height);
        imagealphablending($dest_img, false);
        imagecopyresampled($dest_img, $img, 0, 0, 0, 0, $real_width + intval(ceil(floatval($baseline_offset) / floatval($scale))), $real_height, $width, $height); // Sizes down, for simple antialiasing-like effect
        imagedestroy($img);
        $img = $dest_img;
        if (function_exists('imagesavealpha')) {
            imagesavealpha($img, true);
        }
    }

    header('Content-Type: image/png');
    imagepng($img);
    imagedestroy($img);
}

/**
 * Script to track clicks to external sites.
 *
 * @ignore
 */
function simple_tracker_script()
{
    $url = get_param_string('url');
    if (strpos($url, '://') === false) {
        $url = base64_decode($url);
    }

    $GLOBALS['SITE_DB']->query_insert('link_tracker', array(
        'c_date_and_time' => time(),
        'c_member_id' => get_member(),
        'c_ip_address' => get_ip_address(),
        'c_url' => $url,
    ));

    header('Location: ' . str_replace("\r", '', str_replace("\n", '', $url)));
}

/**
 * Script to show previews of content being added/edited.
 *
 * @ignore
 */
function preview_script()
{
    disable_browser_xss_detection();

    require_code('preview');

    $result = build_preview(true);
    list($output, $validation, $keyword_density, $spelling, $has_device_preview_modes) = $result;

    if (get_param_integer('js_only', 0) == 0) {
        $output = do_template('PREVIEW_SCRIPT', array(
            '_GUID' => '97bd8909e8b9983a0bbf7ab68fab92f3',
            'OUTPUT' => $output->evaluate(),
            'WEBSTANDARDS' => $validation,
            'KEYWORD_DENSITY' => $keyword_density,
            'SPELLING' => $spelling,
            'HIDDEN' => build_keep_post_fields(),
            'HAS_DEVICE_PREVIEW_MODES' => $has_device_preview_modes,
        ));

        $tpl = do_template('STANDALONE_HTML_WRAP', array(
            '_GUID' => '0a96e3b9be154e8b29bee5b1c1c7cc69',
            'TITLE' => do_lang_tempcode('PREVIEW'),
            'FRAME' => true,
            'TARGET' => '_top',
            'CONTENT' => $output,
        ));
    } else {
        $tpl = $output;
    }
    $tpl->handle_symbol_preprocessing();
    $tpl->evaluate_echo();
}

/**
 * Script to perform Composr CRON jobs called by the real CRON.
 *
 * @param  PATH $caller File path of the cron_bridge.php script
 *
 * @ignore
 */
function cron_bridge_script($caller)
{
    if (php_function_allowed('set_time_limit')) {
        set_time_limit(1000); // May get overridden lower later on
    }

    // In query mode, Composr will just give advice on CRON settings to use
    if (get_param_integer('querymode', 0) == 1) {
        header('Content-type: text/plain; charset=' . get_charset());
        safe_ini_set('ocproducts.xss_detect', '0');
        require_code('files2');
        $php_path = find_php_path();
        echo $php_path . ' -C -q --no-header ' . $caller;
        exit();
    }

    // For multi-site installs, run for each install
    global $CURRENT_SHARE_USER, $SITE_INFO;
    if (($CURRENT_SHARE_USER === null) && (!empty($SITE_INFO['custom_share_domain']))) {
        require_code('files');

        foreach ($SITE_INFO as $key => $val) {
            if (substr($key, 0, 12) == 'custom_user_') {
                $url = preg_replace('#://[\w\.]+#', '://' . substr($key, 12) . '.' . $SITE_INFO['custom_share_domain'], get_base_url()) . '/data/cron_bridge.php';
                http_download_file($url);
            }
        }
    }

    if (intval(get_value('last_cron')) < time() - 60 * 60 * 12) {
        decache('main_staff_checklist'); // So the block knows CRON has run
    }

    $limit_hook = get_param_string('limit_hook', '');

    $_log_file = get_custom_file_base() . '/data_custom/cron_log.txt';
    $log_file = mixed();
    if (is_file($_log_file)) {
        $log_file = fopen($_log_file, 'at');
    }

    // Call the hooks which do the real work
    set_value('last_cron', strval(time()));
    $cron_hooks = find_all_hook_obs('systems', 'cron', 'Hook_cron_');
    foreach ($cron_hooks as $hook => $object) {
        if (($limit_hook != '') && ($limit_hook != $hook)) {
            continue;
        }

        if ($log_file !== null) {
            fwrite($log_file, date('Y-m-d H:i:s') . '  STARTING ' . $hook . "\n");
        }

        // Run, with basic locking support
        if ($GLOBALS['DEV_MODE'] || get_value_newer_than('cron_currently_running__' . $hook, time() - 60 * 5, true) !== '1') {
            set_value('cron_currently_running__' . $hook, '1', true);

            $object->run();

            set_value('cron_currently_running__' . $hook, '0', true);
        }

        if ($log_file !== null) {
            fwrite($log_file, date('Y-m-d H:i:s') . '  FINISHED ' . $hook . "\n");
        }
    }

    if ($log_file !== null) {
        fclose($log_file);
    }

    if (!headers_sent()) {
        header('Content-type: text/plain; charset=' . get_charset());
    }
}

/**
 * Script to handle iframe.
 *
 * @ignore
 */
function iframe_script()
{
    $zone = get_param_string('zone');
    $page = get_page_name();
    $ajax = (get_param_integer('ajax', 0) == 1);

    process_url_monikers($page);

    // AJAX prep
    if ($ajax) {
        prepare_for_known_ajax_response();
    }

    // Check permissions
    $zones = $GLOBALS['SITE_DB']->query_select('zones', array('*'), array('zone_name' => $zone), '', 1);
    if (!array_key_exists(0, $zones)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
    }
    if ($zones[0]['zone_require_session'] == 1) {
        set_no_clickjacking_csp();
    }
    if (($zones[0]['zone_name'] != '') && (get_value('windows_auth_is_enabled') !== '1') && ((get_session_id() == '') || (!$GLOBALS['SESSION_CONFIRMED_CACHE'])) && (!is_guest()) && ($zones[0]['zone_require_session'] == 1)) {
        access_denied('ZONE_ACCESS_SESSION');
    }
    if (!has_actual_page_access(get_member(), $page, $zone)) {
        access_denied('ZONE_ACCESS');
    }

    // Closed site
    $site_closed = get_option('site_closed');
    if (($site_closed == '1') && (!has_privilege(get_member(), 'access_closed_site')) && (!$GLOBALS['IS_ACTUALLY_ADMIN'])) {
        header('Content-type: text/plain; charset=' . get_charset());
        @exit(get_option('closed'));
    }

    // SEO
    require_code('site');
    attach_to_screen_header('<meta name="robots" content="noindex" />'); // XHTMLXHTML

    // Load page
    $output = request_page($page, true);

    // Simple AJAX output?
    if ($ajax) {
        safe_ini_set('ocproducts.xss_detect', '0');

        $output->handle_symbol_preprocessing();
        echo $output->evaluate();
        return;
    }

    // Normal output
    $tpl = do_template('STANDALONE_HTML_WRAP', array('_GUID' => '04cf4ef7aac4201bb985327ec0e04c87', 'OPENS_BELOW' => get_param_integer('opens_below', 0) == 1, 'FRAME' => true, 'TARGET' => '_top', 'CONTENT' => $output));
    $tpl->handle_symbol_preprocessing();
    $tpl->evaluate_echo();

    require_code('site');
    save_static_caching($tpl);
}

/**
 * Redirect the browser to where a page_link specifies.
 *
 * @ignore
 */
function page_link_redirect_script()
{
    $page_link = get_param_string('id');
    $tpl = symbol_tempcode('PAGE_LINK', array($page_link));

    $x = $tpl->evaluate();

    if ((strpos($x, "\n") !== false) || (strpos($x, "\r") !== false)) {
        log_hack_attack_and_exit('HEADER_SPLIT_HACK');
    }

    header('Location: ' . $x);
}

/**
 * Outputs the page-link chooser popup.
 *
 * @ignore
 */
function page_link_chooser_script()
{
    // Check we are allowed here
    if (!has_zone_access(get_member(), 'adminzone')) {
        access_denied('ZONE_ACCESS');
    }

    require_lang('menus');

    require_javascript('ajax');
    require_javascript('tree_list');

    require_code('site');
    attach_to_screen_header('<meta name="robots" content="noindex" />'); // XHTMLXHTML

    // Display
    $content = do_template('PAGE_LINK_CHOOSER', array('_GUID' => '235d969528d7b81aeb17e042a17f5537', 'NAME' => 'tree_list'));
    $echo = do_template('STANDALONE_HTML_WRAP', array('_GUID' => '58768379196d6ad27d6298134e33fabd', 'TITLE' => do_lang_tempcode('CHOOSE'), 'CONTENT' => $content, 'POPUP' => true));
    $echo->handle_symbol_preprocessing();
    $echo->evaluate_echo();
}

/**
 * Shows an HTML page of all emoticons clickably.
 *
 * @ignore
 */
function emoticons_script()
{
    if (get_forum_type() != 'cns') {
        warn_exit(do_lang_tempcode('NO_CNS'));
    }

    require_css('cns');

    require_lang('cns');
    require_javascript('editing');

    $extra = has_privilege(get_member(), 'use_special_emoticons') ? '' : ' AND e_is_special=0';
    $_rows = $GLOBALS['FORUM_DB']->query('SELECT * FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_emoticons WHERE e_relevance_level<3' . $extra);

    // Work out what grid spacing to use
    $max_emoticon_width = 0;
    require_code('images');
    foreach ($_rows as $myrow) {
        $test = cms_getimagesize(find_theme_image($myrow['e_theme_img_code'], true));
        if ($test !== false) {
            list($_width,) = $test;
            $max_emoticon_width = max($max_emoticon_width, $_width);
        }
    }
    if ($max_emoticon_width == 0) {
        $max_emoticon_width = 36;
    }
    $padding = 2;
    $window_width = 300;
    $cols = intval(floor(floatval($window_width) / floatval($max_emoticon_width + $padding)));
    if ($cols == 0) {
        $cols = 1;
    }

    // Render UI
    $rows = array();
    $cells = array();
    foreach ($_rows as $i => $myrow) {
        if (($i % $cols == 0) && ($i != 0)) {
            $rows[] = array('CELLS' => $cells);
            $cells = array();
        }

        $code_esc = $myrow['e_code'];
        $cells[] = array(
            '_GUID' => 'ddb838e6fa296df41299c8758db92f8d',
            'COLS' => strval($cols),
            'FIELD_NAME' => filter_naughty_harsh(get_param_string('field_name', 'post')),
            'CODE_ESC' => $code_esc,
            'THEME_IMG_CODE' => $myrow['e_theme_img_code'],
            'CODE' => $myrow['e_code'],
        );
    }
    if ($cells !== array()) {
        $rows[] = array('CELLS' => $cells);
    }

    $content = do_template('CNS_EMOTICON_TABLE', array('_GUID' => 'fb8c4c51f57cd8334800ef12e60d2a8a', 'ROWS' => $rows));

    require_code('site');
    attach_to_screen_header('<meta name="robots" content="noindex" />'); // XHTMLXHTML

    $echo = do_template('STANDALONE_HTML_WRAP', array('_GUID' => '8acac778b145bfe7b063317fbcae7fde', 'TITLE' => do_lang_tempcode('EMOTICONS_POPUP'), 'POPUP' => true, 'CONTENT' => $content));
    $echo->handle_symbol_preprocessing();
    $echo->evaluate_echo();
}

/**
 * Allows conversion of a URL to a thumbnail via a simple script.
 *
 * @ignore
 */
function thumb_script()
{
    $url_full = get_param_string('url');
    if (strpos($url_full, '://') === false) {
        $url_full = base64_decode($url_full);
    }

    require_code('images');

    $new_name = url_to_filename($url_full);
    $file_thumb = get_custom_file_base() . '/uploads/auto_thumbs/' . $new_name;
    if (!file_exists($file_thumb)) {
        $url_thumb = convert_image($url_full, $file_thumb, -1, -1, intval(get_option('thumb_width')), false);
    } else {
        $url_thumb = get_custom_base_url() . '/uploads/auto_thumbs/' . rawurlencode($new_name);
    }

    require_code('mime_types');
    $mime_type = get_mime_type($url_thumb, false);
    header('Content-Type: ' . $mime_type . '; authoritative=true;');

    if ((strpos($url_thumb, "\n") !== false) || (strpos($url_thumb, "\r") !== false)) {
        log_hack_attack_and_exit('HEADER_SPLIT_HACK');
    }
    header('Location: ' . $url_thumb);
}

/**
 * Outputs a modal question dialog.
 *
 * @ignore
 */
function question_ui_script()
{
    safe_ini_set('ocproducts.xss_detect', '0');
    $GLOBALS['SCREEN_TEMPLATE_CALLED'] = '';

    $title = get_param_string('window_title', false, true);
    $_message = nl2br(escape_html(get_param_string('message', false, true)));
    if (function_exists('ocp_mark_as_escaped')) {
        ocp_mark_as_escaped($_message);
    }
    $button_set = explode(',', get_param_string('button_set', false, true));
    $_image_set = get_param_string('image_set', false, true);
    $image_set = ($_image_set == '') ? array() : explode(',', $_image_set);
    $message = do_template('QUESTION_UI_BUTTONS', array('_GUID' => '0c5a1efcf065e4281670426c8fbb2769', 'TITLE' => $title, 'IMAGES' => $image_set, 'BUTTONS' => $button_set, 'MESSAGE' => $_message));

    require_code('site');
    attach_to_screen_header('<meta name="robots" content="noindex" />'); // XHTMLXHTML

    $echo = do_template('STANDALONE_HTML_WRAP', array('_GUID' => '8d72daa4c9f922656b190b643a6fe61d', 'TITLE' => escape_html($title), 'POPUP' => true, 'CONTENT' => $message));
    $echo->handle_symbol_preprocessing();
    $echo->evaluate_echo();
}

/**
 * Proxy an external URL.
 *
 * @ignore
 */
function external_url_proxy_script()
{
    $url = get_param_string('url', false, true);

    // Don't allow loops
    if (strpos($url, 'external_url_proxy.php') !== false) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    // Don't allow non-HTTP(S)
    if (preg_match('#^https?://#', $url) == 0) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    // No time-limits wanted
    if (php_function_allowed('set_time_limit')) {
        set_time_limit(0);
    }

    // Can't add in compression
    safe_ini_set('zlib.output_compression', 'Off');

    // No ocProducts XSS filter
    safe_ini_set('ocproducts.xss_detect', '0');

    // Stream
    $content_type = 'application/octet-stream';
    $f = @fopen($url, 'rb');
    if (isset($http_response_header)) {
        // Work out appropriate content type (with restrictions)
        require_code('mime_types');
        $mime_types = array_flip(get_mime_types(false));
        $matches = array();
        foreach ($http_response_header as $header) {
            if (preg_match('#^Content-Type:\s*(.*)\s*#i', $header, $matches) != 0) {
                $content_type = $matches[1];
            }
        }
        if (!isset($mime_types[$content_type])) {
            $content_type = 'application/octet-stream';
        }
        header('Content-Type: ' . $content_type);

        foreach ($http_response_header as $header) {
            if (preg_match('#^Content-Type:\s*(.*)\s*#i', $header) == 0) {
                header($header);
            }
        }
    } else {
        header('Content-Type: ' . $content_type);
    }
    if ($f !== false) {
        fpassthru($f);
        @fclose($f);
    }
}
