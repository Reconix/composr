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
 * Geocode a written location.
 *
 * @param  string $location Written location
 * @param  ?Tempcode $error_msg Error message (written by reference) (null: not returned)
 * @return ?array A pair: Latitude, Longitude (null: error)
 */
function geocode($location, &$error_msg = null)
{
    $url_params = '&address=' . urlencode($location);
    $result = _google_geocode($url_params, $error_msg);
    if ($result === null) {
        return null;
    }

    if (!isset($result['results'][0])) {
        $error_msg = do_lang_tempcode('GOOGLE_GEOCODE_INCOMPLETE');
        return null;
    }

    $r = $result['results'][0];

    if (!isset($r['geometry']['location'])) {
        $error_msg = do_lang_tempcode('GOOGLE_GEOCODE_INCOMPLETE');
        return null;
    }

    $latitude = $r['geometry']['location']['lat'];
    $longitude = $r['geometry']['location']['lng'];

    return array($latitude, $longitude);
}

/**
 * Geocode a latitude & longitude.
 *
 * @param  float $latitude Latitude
 * @param  float $longitude Longitude
 * @param  ?Tempcode $error_msg Error message (written by reference) (null: not returned)
 * @return ?array A tuple: Formatted address, Street Address, City, County, State, Zip/Postcode, Country (null: error)
 */
function reverse_geocode($latitude, $longitude, &$error_msg = null)
{
    $url_params = '&latlng=' . urlencode(float_to_raw_string($latitude)) . ',' . urlencode(float_to_raw_string($longitude));
    $result = _google_geocode($url_params, $error_msg);
    if ($result === null) {
        return null;
    }

    if (!isset($result['results'][0])) {
        $error_msg = do_lang_tempcode('GOOGLE_GEOCODE_INCOMPLETE');
        return null;
    }

    $r = $result['results'][0];

    $street_address = null;
    $city = null;
    $county = null;
    $state = null;
    $post_code = null;
    $country = null;

    if (!isset($r['formatted_address'])) {
        $error_msg = do_lang_tempcode('GOOGLE_GEOCODE_INCOMPLETE');
        return null;
    }

    foreach ($r['address_components'] as $component) {
        if (in_array('street_address', $component['types'])) {
            $street_address = $component['long_name'];
        } else {
            if (in_array('street_number', $component['types'])) {
                $street_address = $component['long_name'];
            }
            if (in_array('route', $component['types'])) {
                if ($street_address === null) {
                    $street_address = '';
                } else {
                    $street_address .= ' ';
                }
                $street_address .= $component['long_name'];
            }
        }
        if (in_array('locality', $component['types'])) {
            $city = $component['long_name'];
        }
        if (in_array('administrative_area_level_2', $component['types'])) {
            $county = $component['long_name'];
        }
        if (in_array('administrative_area_level_1', $component['types'])) {
            $state = $component['short_name'];
        }
        if (in_array('postal_code', $component['types'])) {
            $post_code = $component['short_name'];
        }
        if (in_array('country', $component['types'])) {
            $country = $component['short_name'];
        }
    }

    return array($r['formatted_address'], $street_address, $city, $county, $state, $post_code, $country);
}

/**
 * Geocode a written location.
 *
 * @param  string $url_params What to add into the URL
 * @param  ?Tempcode $error_msg Error message (written by reference) (null: not returned)
 * @return ?array Geocode results (null: error)
 * @ignore
 */
function _google_geocode($url_params, &$error_msg = null)
{
    // Test to see if we know we were over the limit in the last 24h
    $limit_test = get_value_newer_than('over_geocode_query_limit', time() - 60 * 60 * 24, true);
    if ($limit_test === 1) {
        $error_msg = do_lang_tempcode('GOOGLE_GEOCODE_OVER_QUERY_LIMIT');
        return null;
    }
    
    $key = get_option('google_geocode_api_key');
    /*if ($key == '') { Actually, does work
        $error_msg = do_lang_tempcode('GOOGLE_GEOCODE_API_NOT_CONFIGURED');
        return null;
    }*/

    $url = 'http://maps.googleapis.com/maps/api/geocode/json';
    $url .= '?language=' . urlencode(strtolower(get_site_default_lang()));
    if ($key != '') {
        $url .= '&key=' . urlencode($key);
    }
    $url .= $url_params;

    $_result = http_get_contents($url, array('trigger_error' => false));

    if (empty($_result)) {
        $error_msg = do_lang_tempcode('GOOGLE_GEOCODE_COULD_NOT_CONNECT');
        return null;
    }

    $result = @json_decode($_result, true);
    if (!is_array($result)) {
        $error_msg = do_lang_tempcode('GOOGLE_GEOCODE_COULD_NOT_PARSE');
        return null;
    }

    if ($result['status'] == 'OVER_QUERY_LIMIT') {
        require_lang('locations');
        set_value('over_geocode_query_limit', '1', true);
        $error_msg = do_lang_tempcode('GOOGLE_GEOCODE_OVER_QUERY_LIMIT');
        return null;
    }

    if (isset($result['error_message'])) {
        $error_msg = make_string_tempcode($result['error_message']);
        return null;
    }

    if ($result['status'] == 'ZERO_RESULTS' || $result['status'] == 'REQUEST_DENIED' || $result['status'] == 'UNKNOWN_ERROR') {
        $error_msg = do_lang_tempcode('GOOGLE_GEOCODE_' . $result['status']);
        return null;
    }

    if ($result['status'] != 'OK') { // Usually INVALID_REQUEST; should not happen so raise stack trace
        fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    return $result;
}

