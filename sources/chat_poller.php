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
 * @package    chat
 */

/**
 * Function to quickly (efficiently) check to see if there's been any chat activity.
 */
function chat_poller()
{
    define('CHAT_ACTIVITY_PRUNE', 25); // A little naughty to define this here, as already defined in chat.php

    $message_id = get_param_integer('message_id', -1);
    $event_id = get_param_integer('event_id', -1);

    if (
        ((file_exists(get_custom_file_base() . '/data_custom/modules/chat/chat_last_full_check.dat')) && (filemtime(get_custom_file_base() . '/data_custom/modules/chat/chat_last_full_check.dat') >= time() - intval(floatval(CHAT_ACTIVITY_PRUNE) / 2.0))) && // If we've done a check within CHAT_ACTIVITY_PRUNE/2 seconds don't try again unless something is new (we do need to allow pruning to happen sometimes)
        (($message_id != -1) && (file_exists(get_custom_file_base() . '/data_custom/modules/chat/chat_last_msg.dat')) && (intval(cms_file_get_contents_safe(get_custom_file_base() . '/data_custom/modules/chat/chat_last_msg.dat')) <= $message_id)) &&
        (($event_id != -1) && (file_exists(get_custom_file_base() . '/data_custom/modules/chat/chat_last_event.dat')) && (intval(cms_file_get_contents_safe(get_custom_file_base() . '/data_custom/modules/chat/chat_last_event.dat')) <= $event_id))
    ) {
        /*
        We do let the main code to run this at CHAT_ACTIVITY_PRUNE intervals, so no need to run the commented code below

        require_code('zones'); // Zone is needed because zones are where all Composr pages reside
        require_code('config'); // Config is needed for much active stuff
        require_code('users'); // Users are important due to permissions

        $room_id = get_param_integer('room_id', -1);
        require_code('chat');
        chat_room_prune($room_id);
        */

        chat_null_exit();
    }

    touch(get_custom_file_base() . '/data_custom/modules/chat/chat_last_full_check.dat');
}

/**
 * Exit the code saying "no messages".
 */
function chat_null_exit()
{
    prepare_for_known_ajax_response();

    header('Content-Type: application/xml');

    //  encoding="' . get_charset() . '" not needed due to no data in it
    $output = '<?xml version="1.0" ?' . '><response><result></result></response>';

    exit($output);
}
