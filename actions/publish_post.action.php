<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();


class WPSL_Publish_Post extends WPSL_Core
{
    function __construct()
    {
        global $wpsl_meta;

        $hook = 'publish_post';
        $package = 'core';
        $title = __('New post or post status change', 'wpsl');
        $desc = __('Send Slack notification when a new post publishes or an existing post&#39;s status changes.', 'wpsl');

        $wpsl_meta[] = array(
            'hook'  =>  $hook,
            'package'   =>  $package,
            'title'   =>  $title,
            'desc'   =>  $desc,
            'default'   =>  'yes',
            'order' =>  98
        );

        add_action('transition_post_status', array($this, 'publish_post'), 10, 3);
    }

    public function publish_post($new, $old, $post)
    {

        if($post->post_type != 'post') {
            return;
        }

        if ($new == 'draft' || $new == 'auto-draft' || $new == 'inherit' || $old == $new) {
            return;
        }

        $url = get_home_url();

        $status_list = get_post_stati(array(), 'objects');
        $new_status = $status_list[$new]->label;
        $old_status = $status_list[$old]->label;
        $settings = parent::option('accordion', false, null);
        $active = (empty($settings['publish_post_switch']) || $settings['publish_post_switch'] == 'yes') ? true : false;
        $channel = (empty($settings['publish_post_channel'])) ? false : esc_attr($settings['publish_post_channel']);

        if (!$active) {
            return;
        }

        $id = $post->ID;
        $author = $post->post_author;
        $link = get_permalink($id);
        $date = $post->post_date_gmt;
        $excerpt = strip_tags($post->post_excerpt);
        $title = strip_tags($post->post_title);
        $title = (!empty($title)) ? $title : __('Untitled', 'wpsl');
        $user = get_userdata($author);
        $author_name = $user->display_name;
        $email = $user->user_email;

        if ($new == 'publish') {
            $msg = sprintf(__(':sunglasses: New post `%s` now published on `%s`.', 'wpsl'), $title, $url);
            $msg_attach = sprintf(__('We have a new post by `%s` :heart_eyes:', 'wpsl'), $author_name);
            $title_attach = __('New post', 'wpsl');
            $color = '#36a64f';
        } else {
            $msg = sprintf(__(':zap: Status of post `%s` has been change to `%s` on `%s`.', 'wpsl'), $title, $new_status, $url);
            $msg_attach = sprintf(__('Post status change from `%s` to `%s` :speaker:', 'wpsl'), $old_status, $new_status);
            $title_attach = __('Post status change', 'wpsl');
            if ($new == 'trash') {
                $color = '#d63638';
            } else {
                $color = '#36a64f';
            }
        }

        $attachments =  array(
            "color" => $color,
            "pretext" => $msg,
            "author_name" => $author_name,
            "author_link" => get_edit_profile_url($user->ID),
            "author_icon" => get_avatar_url($email, array('size' =>  16, 'default'   =>  'retro')),
            "title" => $title_attach,
            "title_link" => $link,
            "text" => $msg_attach,
            "fields" => array(
                array(
                    "title" => __('Author', 'wpsl'),
                    "value" => $author_name,
                    "short" => true
                ),
                array(
                    "title" => __('Post Date', 'wpsl'),
                    "value" => $date,
                    "short" => true
                ),
                array(
                    "title" => __('Old status', 'wpsl'),
                    "value" => $old_status,
                    "short" => true
                ),
                array(
                    "title" => __('New status', 'wpsl'),
                    "value" => $new_status,
                    "short" => true
                ),
                array(
                    "title" => __('Title', 'wpsl'),
                    "value" => $title,
                    "short" => false
                ),
                array(
                    "title" => __('Excerpt', 'wpsl'),
                    "value" => (!empty($excerpt)) ? $excerpt : __('Not defined', 'wpsl'),
                    "short" => false
                )
            ),
            "footer" => parent::footer(),
            "ts" => current_time('timestamp', 1)
        );

        do_action('wpsl_run', $attachments, $channel);
    }
}

new WPSL_Publish_Post();
