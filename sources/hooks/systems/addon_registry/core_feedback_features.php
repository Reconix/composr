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
 * @package    core_feedback_features
 */

/**
 * Hook class.
 */
class Hook_addon_registry_core_feedback_features
{
    /**
     * Get a list of file permissions to set
     *
     * @return array File permissions to set
     */
    public function get_chmod_array()
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Features for user interaction with content.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_feedback',
            'tut_adv_news',
        );
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(),
            'recommends' => array(),
            'conflicts_with' => array(),
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/feedback/comment.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/feedback/comment.png',
            'themes/default/images/icons/48x48/feedback/comment.png',
            'themes/default/images/icons/24x24/feedback/comments_topic.png',
            'themes/default/images/icons/48x48/feedback/comments_topic.png',
            'themes/default/images/icons/24x24/feedback/rate.png',
            'themes/default/images/icons/48x48/feedback/rate.png',
            'themes/default/images/icons/24x24/menu/adminzone/audit/trackbacks.png',
            'themes/default/images/icons/48x48/menu/adminzone/audit/trackbacks.png',
            'sources/topics.php',
            'sources/hooks/systems/notifications/like.php',
            'sources/hooks/systems/notifications/comment_posted.php',
            'themes/default/templates/TRACKBACK_DELETE_SCREEN.tpl',
            'sources/hooks/systems/page_groupings/trackbacks.php',
            'lang/EN/trackbacks.ini',
            'sources/hooks/systems/trackback/.htaccess',
            'sources/hooks/systems/trackback/index.html',
            'data/trackback.php',
            'adminzone/pages/modules/admin_trackbacks.php',
            'sources/hooks/systems/addon_registry/core_feedback_features.php',
            'sources/hooks/systems/snippets/rating.php',
            'sources/hooks/systems/snippets/comments.php',
            'sources/hooks/systems/preview/comments.php',
            'themes/default/images/1x/like.png',
            'themes/default/images/1x/dislike.png',
            'themes/default/images/2x/like.png',
            'themes/default/images/2x/dislike.png',
            'sources/hooks/systems/rss/comments.php',
            'themes/default/templates/COMMENTS_POSTING_FORM.tpl',
            'themes/default/templates/COMMENTS_WRAPPER.tpl',
            'themes/default/templates/COMMENTS_DEFAULT_TEXT.tpl',
            'themes/default/templates/RATING_BOX.tpl',
            'themes/default/templates/RATING_INLINE_STATIC.tpl',
            'themes/default/templates/RATING_INLINE_DYNAMIC.tpl',
            'themes/default/templates/RATING_DISPLAY_SHARED.tpl',
            'themes/default/templates/RATING_FORM.tpl',
            'themes/default/templates/TRACKBACK.tpl',
            'themes/default/templates/TRACKBACK_WRAPPER.tpl',
            'themes/default/xml/TRACKBACK_XML.xml',
            'themes/default/xml/TRACKBACK_XML_ERROR.xml',
            'themes/default/xml/TRACKBACK_XML_LISTING.xml',
            'themes/default/xml/TRACKBACK_XML_NO_ERROR.xml',
            'themes/default/xml/TRACKBACK_XML_WRAPPER.xml',
            'sources/feedback.php',
            'sources/feedback2.php',
            'pages/comcode/EN/feedback.txt',
            'sources/blocks/main_comments.php',
            'sources/blocks/main_trackback.php',
            'sources/blocks/main_rating.php',
            'themes/default/templates/COMMENT_AJAX_HANDLER.tpl',
            'data/post_comment.php',
            'sources/hooks/systems/config/max_thread_depth.php',
            'sources/hooks/systems/config/comment_topic_subject.php',
            'sources/hooks/systems/config/default_comment_sort_order.php',
            'sources/hooks/systems/config/comments_to_show_in_thread.php',
            'sources/hooks/systems/config/simplify_wysiwyg_by_permissions.php',
            'sources/hooks/systems/config/allow_own_rate.php',
            'sources/hooks/systems/config/enable_feedback.php',
            'sources/hooks/systems/config/is_on_comments.php',
            'sources/hooks/systems/config/is_on_rating.php',
            'sources/hooks/systems/config/is_on_trackbacks.php',
            'sources/hooks/systems/symbols/SHOW_RATINGS.php',
            'themes/default/templates/RATINGS_SHOW.tpl',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/COMMENTS_DEFAULT_TEXT.tpl' => 'comments_default_text',
            'templates/TRACKBACK.tpl' => 'administrative__trackback_delete_screen',
            'templates/TRACKBACK_DELETE_SCREEN.tpl' => 'administrative__trackback_delete_screen',
            'xml/TRACKBACK_XML_NO_ERROR.xml' => 'trackback_xml_wrapper',
            'xml/TRACKBACK_XML_ERROR.xml' => 'trackback_xml_wrapper',
            'xml/TRACKBACK_XML_WRAPPER.xml' => 'trackback_xml_wrapper',
            'templates/COMMENTS_POSTING_FORM.tpl' => 'comments',
            'templates/RATING_BOX.tpl' => 'rating',
            'templates/RATING_DISPLAY_SHARED.tpl' => 'rating_display_shared',
            'templates/RATINGS_SHOW.tpl' => 'ratings_show',
            'templates/COMMENTS_WRAPPER.tpl' => 'comments_wrapper',
            'xml/TRACKBACK_XML.xml' => 'trackback_xml_wrapper',
            'templates/TRACKBACK_WRAPPER.tpl' => 'trackback_wrapper',
            'xml/TRACKBACK_XML_LISTING.xml' => 'trackback_xml_listing',
            'templates/RATING_FORM.tpl' => 'rating',
            'templates/RATING_INLINE_STATIC.tpl' => 'rating_inline_static',
            'templates/RATING_INLINE_DYNAMIC.tpl' => 'rating_inline_dynamic',
            'templates/EMOTICON_CLICK_CODE.tpl' => 'comments',
            'templates/COMMENT_AJAX_HANDLER.tpl' => 'comments',
            'templates/POST.tpl' => 'comments_wrapper',
            'templates/POST_CHILD_LOAD_LINK.tpl' => 'comments_wrapper',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__comments_default_text()
    {
        return array(
            lorem_globalise(do_lorem_template('COMMENTS_DEFAULT_TEXT', array()), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__trackback_delete_screen()
    {
        $trackbacks = new Tempcode();
        foreach (placeholder_array() as $k => $value) {
            $trackbacks->attach(do_lorem_template('TRACKBACK', array(
                'ID' => strval($k),
                'TIME_RAW' => placeholder_date_raw(),
                'TIME' => placeholder_number(),
                'URL' => placeholder_url(),
                'TITLE' => lorem_word(),
                'EXCERPT' => lorem_phrase(),
                'NAME' => $value,
            )));
        }

        return array(
            lorem_globalise(do_lorem_template('TRACKBACK_DELETE_SCREEN', array(
                'TITLE' => lorem_title(),
                'TRACKBACKS' => $trackbacks,
                'LOTS' => lorem_phrase(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__trackback_xml_wrapper()
    {
        $xml = do_lorem_template('TRACKBACK_XML', array(
            'TITLE' => lorem_phrase(),
            'LINK' => placeholder_url(),
            'EXCERPT' => lorem_phrase(),
        ), null, false, null, '.xml', 'xml');
        $xml->attach(do_lorem_template('TRACKBACK_XML_NO_ERROR', array(), null, false, null, '.xml', 'xml'));
        $xml->attach(do_lorem_template('TRACKBACK_XML_ERROR', array('TRACKBACK_ERROR' => lorem_phrase()), null, false, null, '.xml', 'xml'));
        return array(
            do_lorem_template('TRACKBACK_XML_WRAPPER', array(
                'XML' => $xml,
            ), null, false, null, '.xml', 'xml')
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__comments()
    {
        require_lang('comcode');
        require_javascript('plupload');
        require_javascript('posting');

        require_css('forms');

        $content = do_lorem_template('ATTACHMENT', array(
            'I' => placeholder_number(),
            'POSTING_FIELD_NAME' => '',
        ));

        $attachments = do_lorem_template('ATTACHMENTS', array(
            'ATTACHMENT_TEMPLATE' => $content,
            'IMAGE_TYPES' => placeholder_types(),
            'POSTING_FIELD_NAME' => '',
            'ATTACHMENTS' => $content,
            'MAX_ATTACHMENTS' => placeholder_number(),
            'NUM_ATTACHMENTS' => placeholder_number(),
        ));

        $ret = do_lorem_template('COMMENTS_POSTING_FORM', array(
            'JOIN_BITS' => lorem_phrase_html(),
            'ATTACHMENTS' => $attachments,
            'ATTACH_SIZE_FIELD' => '',
            'POST_WARNING' => lorem_phrase(),
            'COMMENT_TEXT' => lorem_sentence_html(),
            'GET_EMAIL' => lorem_word_html(),
            'EMAIL_OPTIONAL' => lorem_word_html(),
            'GET_TITLE' => true,
            'EM' => placeholder_emoticon_chooser(),
            'DISPLAY' => 'block',
            'COMMENT_URL' => placeholder_url(),
            'SUBMIT_NAME' => lorem_word(),
            'TITLE' => lorem_word(),
            'MAKE_POST' => true,
            'CREATE_TICKET_MAKE_POST' => true,
            'FIRST_POST' => lorem_paragraph_html(),
            'FIRST_POST_URL' => placeholder_url(),
            'NAME' => 'field',
        ));

        $ret->attach(do_lorem_template('COMMENT_AJAX_HANDLER', array(
            'OPTIONS' => '',
            'IS_THREADED' => false,
            'HASH' => '',
        )));

        return array(
            lorem_globalise($ret, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__comments_wrapper()
    {
        $review_titles = array();
        $review_titles[] = array(
            'REVIEW_TITLE' => lorem_phrase(),
            'REVIEW_RATING' => make_string_tempcode(float_format(10.0)),
        );
        $comments = new Tempcode();
        foreach (placeholder_array() as $i => $comment) {
            $map = array(
                'INDIVIDUAL_REVIEW_RATINGS' => array(),
                'HIGHLIGHT' => ($i == 1),
                'TITLE' => lorem_word(),
                'TIME_RAW' => placeholder_number(),
                'TIME' => placeholder_date(),
                'POSTER_ID' => placeholder_id(),
                'POSTER_URL' => placeholder_url(),
                'POSTER_NAME' => lorem_word(),
                'POSTER' => null,
                'POSTER_DETAILS' => new Tempcode(),
                'ID' => placeholder_id() . strval($i),
                'POST' => lorem_phrase(),
                'POST_COMCODE' => lorem_phrase(),
                'CHILDREN' => lorem_phrase(),
                'OTHER_IDS' => array(
                    placeholder_id()
                ),
                'RATING' => new Tempcode(),
                'EMPHASIS' => new Tempcode(),
                'BUTTONS' => new Tempcode(),
                'LAST_EDITED_RAW' => '',
                'LAST_EDITED' => new Tempcode(),
                'UNVALIDATED' => new Tempcode(),
                'TOPIC_ID' => placeholder_id(),
                'IS_SPACER_POST' => false,
                'IS_THREADED' => false,
                'NUM_TO_SHOW_LIMIT' => placeholder_number(),
            );
            $comments->attach(do_lorem_template('POST', $map));
            do_lorem_template('POST_CHILD_LOAD_LINK', $map); // INCLUDE'd in above, but test set needs to see it run direct
        }

        if (addon_installed('captcha')) {
            require_code('captcha');
            $use_captcha = use_captcha();
        } else {
            $use_captcha = false;
        }
        $form = do_lorem_template('COMMENTS_POSTING_FORM', array(
            'FIRST_POST_URL' => '',
            'JOIN_BITS' => lorem_phrase_html(),
            'FIRST_POST' => lorem_paragraph_html(),
            'TYPE' => 'downloads',
            'ID' => placeholder_id(),
            'REVIEW_RATING_CRITERIA' => $review_titles,
            'USE_CAPTCHA' => $use_captcha,
            'GET_EMAIL' => false,
            'EMAIL_OPTIONAL' => true,
            'GET_TITLE' => true,
            'POST_WARNING' => do_lang('POST_WARNING'),
            'COMMENT_TEXT' => get_option('comment_text'),
            'EM' => placeholder_emoticon_chooser(),
            'DISPLAY' => 'block',
            'COMMENT_URL' => placeholder_url(),
            'TITLE' => lorem_word(),
            'MAKE_POST' => true,
            'CREATE_TICKET_MAKE_POST' => true,
            'NAME' => 'field',
        ));

        $out = do_lorem_template('COMMENTS_WRAPPER', array(
            'TYPE' => lorem_phrase(),
            'ID' => placeholder_id(),
            'REVIEW_RATING_CRITERIA' => $review_titles,
            'AUTHORISED_FORUM_URL' => placeholder_url(),
            'FORM' => $form,
            'COMMENTS' => $comments,
            'SORT' => 'relevance',
            'TOTAL_POSTS' => placeholder_number(),
            'IS_THREADED' => false,
        ));

        $out->attach(do_lorem_template('COMMENT_AJAX_HANDLER', array(
            'OPTIONS' => '',
            'IS_THREADED' => false,
            'HASH' => '',
        )));

        return array(
            lorem_globalise($out, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__trackback_wrapper()
    {
        $trackbacks = placeholder_array();

        $content = new Tempcode();

        foreach ($trackbacks as $i => $value) {
            $content->attach(do_lorem_template('TRACKBACK', array(
                'ID' => placeholder_id() . strval($i),
                'TIME_RAW' => placeholder_date(),
                'TIME' => placeholder_date(),
                'URL' => placeholder_url(),
                'TITLE' => lorem_word(),
                'EXCERPT' => '',
                'NAME' => placeholder_id(),
            )));
        }

        return array(
            lorem_globalise(do_lorem_template('TRACKBACK_WRAPPER', array(
                'TRACKBACKS' => $content,
                'TRACKBACK_PAGE' => lorem_word(),
                'TRACKBACK_ID' => placeholder_id(),
                'TRACKBACK_TITLE' => lorem_phrase(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__trackback_xml_listing()
    {
        $content = do_lorem_template('TRACKBACK_XML_LISTING', array(
            'ITEMS' => lorem_phrase(),
            'LINK_PAGE' => lorem_word(),
            'LINK_ID' => placeholder_id(),
        ), null, false, null, '.xml', 'xml');

        return array(
            $content
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__rating()
    {
        $all_rating_criteria = array();
        $all_rating_criteria[] = array(
            'TITLE' => lorem_word(),
            'RATING' => make_string_tempcode('6'),
            'NUM_RATINGS' => placeholder_number(),
            'TYPE' => lorem_word(),
        );
        $rating_form = do_lorem_template('RATING_FORM', array(
            'LIKES' => true,
            'CONTENT_TYPE' => 'downloads',
            'ID' => placeholder_id(),
            'URL' => placeholder_url(),
            'ALL_RATING_CRITERIA' => $all_rating_criteria,
            'HAS_RATINGS' => true,
            'OVERALL_NUM_RATINGS' => placeholder_number(),
            'SIMPLISTIC' => true,
            'ERROR' => '',
            'CONTENT_URL' => placeholder_url(),
            'CONTENT_TITLE' => lorem_phrase(),
        ));

        return array(
            lorem_globalise(do_lorem_template('RATING_BOX', array(
                'OVERALL_NUM_RATINGS' => placeholder_number(),
                'LIKES' => true,
                'CONTENT_TYPE' => 'downloads',
                'ID' => placeholder_id(),
                'HAS_RATINGS' => true,
                'ALL_RATING_CRITERIA' => $all_rating_criteria,
                'NUM_RATINGS' => '10',
                'RATING_FORM' => $rating_form,
                'ERROR' => '',
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__rating_inline_static()
    {
        $all_rating_criteria = array();
        foreach (placeholder_array() as $i => $v) {
            $all_rating_criteria[] = array(
                'TITLE' => lorem_word(),
                'RATING' => '3',
                'OVERALL_NUM_RATINGS' => placeholder_number(),
                'TYPE' => lorem_word() . strval($i),
            );
        }
        $rating_form = do_lorem_template('RATING_FORM', array(
            'CONTENT_TYPE' => lorem_word(),
            'ID' => placeholder_id(),
            'URL' => placeholder_url(),
            'LIKES' => true,
            'ALL_RATING_CRITERIA' => $all_rating_criteria,
            'HAS_RATINGS' => true,
            'SIMPLISTIC' => false,
            'ERROR' => '',
            'CONTENT_URL' => placeholder_url(),
            'CONTENT_TITLE' => lorem_phrase(),
        ));
        return array(
            lorem_globalise(do_lorem_template('RATING_INLINE_STATIC', array(
                'CONTENT_TYPE' => lorem_word(),
                'ID' => placeholder_id(),
                'ALL_RATING_CRITERIA' => $all_rating_criteria,
                'HAS_RATINGS' => true,
                'NUM_RATINGS' => placeholder_number(),
                'OVERALL_NUM_RATINGS' => placeholder_number(),
                'RATING_FORM' => $rating_form,
                'ERROR' => '',
                'LIKES' => false,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__rating_inline_dynamic()
    {
        $all_rating_criteria = array();
        foreach (placeholder_array() as $i => $v) {
            $all_rating_criteria[] = array(
                'TITLE' => lorem_word(),
                'RATING' => '3',
                'OVERALL_NUM_RATINGS' => placeholder_number(),
                'TYPE' => lorem_word() . strval($i),
            );
        }
        $rating_form = do_lorem_template('RATING_FORM', array(
            'CONTENT_TYPE' => lorem_word(),
            'ID' => placeholder_id(),
            'URL' => placeholder_url(),
            'ALL_RATING_CRITERIA' => $all_rating_criteria,
            'HAS_RATINGS' => true,
            'SIMPLISTIC' => false,
            'ERROR' => '',
            'LIKES' => true,
            'CONTENT_URL' => placeholder_url(),
            'CONTENT_TITLE' => lorem_phrase(),
        ));
        return array(
            lorem_globalise(do_lorem_template('RATING_INLINE_DYNAMIC', array(
                'CONTENT_TYPE' => lorem_word(),
                'ID' => placeholder_id(),
                'ALL_RATING_CRITERIA' => $all_rating_criteria,
                'HAS_RATINGS' => true,
                'NUM_RATINGS' => placeholder_number(),
                'OVERALL_NUM_RATINGS' => placeholder_number(),
                'RATING_FORM' => $rating_form,
                'ERROR' => '',
                'LIKES' => false,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__rating_display_shared()
    {
        $all_rating_criteria = array();
        foreach (placeholder_array() as $i => $v) {
            $all_rating_criteria[] = array(
                'TITLE' => lorem_word(),
                'RATING' => '3',
                'OVERALL_NUM_RATINGS' => placeholder_number(),
                'TYPE' => lorem_word() . strval($i),
            );
        }
        $rating_form = do_lorem_template('RATING_FORM', array(
            'CONTENT_TYPE' => lorem_word(),
            'ID' => placeholder_id(),
            'URL' => placeholder_url(),
            'ALL_RATING_CRITERIA' => $all_rating_criteria,
            'HAS_RATINGS' => true,
            'SIMPLISTIC' => false,
            'ERROR' => '',
            'LIKES' => true,
            'CONTENT_URL' => placeholder_url(),
            'CONTENT_TITLE' => lorem_phrase(),
        ));
        return array(
            lorem_globalise(do_lorem_template('RATING_DISPLAY_SHARED', array(
                'CONTENT_TYPE' => lorem_word(),
                'RATING' => '3',
                'ID' => placeholder_id(),
                'ALL_RATING_CRITERIA' => $all_rating_criteria,
                'HAS_RATINGS' => true,
                'NUM_RATINGS' => placeholder_number(),
                'OVERALL_NUM_RATINGS' => placeholder_number(),
                'RATING_FORM' => $rating_form,
                'ERROR' => '',
                'LIKES' => false,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__ratings_show()
    {
        $ratings = array();
        $ratings[] = array(
            'RATING_MEMBER' => placeholder_id(),
            'RATING_USERNAME' => lorem_word(),
            'RATING_IP' => lorem_word(),
            'RATING_TIME' => placeholder_date_raw(),
            'RATING_TIME_FORMATTED' => placeholder_date(),
            'RATING' => '2',
        );

        return array(
            lorem_globalise(do_lorem_template('RATINGS_SHOW', array(
                'RATINGS' => $ratings,
                'HAS_MORE' => true,
                'MAX' => '1',
                'CNT' => '1',
                'CNT_REMAINING' => '10',
            )), null, '', true)
        );
    }
}
