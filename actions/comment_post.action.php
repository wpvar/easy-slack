<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

class WPSL_Comment_Post extends WPSL_Core
{
    function __construct()
    {
        global $wpsl_meta;

        $hook = 'comment_post';
        $package = 'core';
        $title = __('New comment', 'wpsl');
        $desc = __('Send Slack notification when there is a new comment submission or pending review.', 'wpsl');

        $wpsl_meta[] = array(
            'hook'  =>  $hook,
            'package'   =>  $package,
            'title'   =>  $title,
            'desc'   =>  $desc,
            'default'   =>  'yes',
            'order' =>  99
        );

        add_action('comment_post', array($this, 'comment_post'), 10, 3);
    }

    public function comment_post($comment_ID, $comment_approved, $commentdata)
    {
        $settings = parent::option('accordion', false, null);
        $active = (empty($settings['comment_post_switch']) || $settings['comment_post_switch'] == 'yes') ? true : false;
        $channel = (empty($settings['comment_post_channel'])) ? false : esc_attr($settings['comment_post_channel']);

        if (!$active) {
            return;
        }

        $author = get_comment_author($comment_ID);
        $link = get_permalink($commentdata['comment_post_ID']);
        $email = $commentdata['comment_author_email'];
        $title = get_the_title($commentdata['comment_post_ID']);
        $content = esc_html(strip_tags($commentdata['comment_content']));

        if ($comment_approved == 1) {
            $color = '#36a64f';
            $status = ':white_check_mark: *Aprroved*';
            $smily = ':green_book:';
        }
        if ($comment_approved == 0) {
            $color = '#ff9800';
            $status = ':clock4: *Pending*';
            $smily = ':orange_book:';
        }

        if ($comment_approved != 1 && $comment_approved != 0) {
            return;
        }

        $attachments =  array(
            "color" => $color,
            "pretext" => sprintf(__('%s User: `%s` posted a new comment.', 'wpsl'), $smily, $author),
            "author_name" => $author,
            "author_link" => $link,
            "author_icon" => get_avatar_url($email, array('size' =>  16, 'default'   =>  'retro')),
            "title" => __('New comment', 'wpsl'),
            "title_link" => $link,
            "text" => $content,
            "thumb_url" => get_avatar_url($email, array('size' =>  200, 'default'   =>  'retro')),
            "fields" => array(
                array(
                    "title" => __('Username', 'wpsl'),
                    "value" => $author,
                    "short" => true
                ),
                array(
                    "title" => __('Date', 'wpsl'),
                    "value" => $commentdata['comment_date_gmt'],
                    "short" => true
                ),
                array(
                    "title" => __('Status', 'wpsl'),
                    "value" => $status,
                    "short" => true
                ),
                array(
                    "title" => __('Post', 'wpsl'),
                    "value" => '<' . $link . '|' . $title . '>',
                    "short" => true
                ),
            ),
            "footer" => parent::footer(),
            "ts" => current_time('timestamp', 1)
        );

        do_action('wpsl_run', $attachments, $channel);
    }
}

new WPSL_Comment_Post();
