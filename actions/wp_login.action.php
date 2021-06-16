<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

class WPSL_Wp_Login extends WPSL_Core
{
    function __construct()
    {
        global $wpsl_meta;

        $hook = 'wp_login';
        $package = 'core';
        $title = __('User login success', 'wpsl');
        $desc = __('Send Slack notification when users or admins successfully logging in.', 'wpsl');

        $wpsl_meta[] = array(
            'hook'  =>  $hook,
            'package'   =>  $package,
            'title'   =>  $title,
            'desc'   =>  $desc,
            'default'   =>  'yes',
            'order' =>  101
        );

        add_action('wp_login', array($this, 'wp_login'), 10, 2);
    }

    public function wp_login($user_login, $user)
    {
        $url = get_home_url();

        $settings = parent::option('accordion', false, null);
        $active = (empty($settings['wp_login_failed_switch']) || $settings['wp_login_failed_switch'] == 'yes') ? true : false;
        $channel = (empty($settings['wp_login_failed_channel'])) ? false : esc_attr($settings['wp_login_failed_channel']);

        if (!$active) {
            return;
        }

        $link = get_edit_profile_url($user->ID);
        $avatar_small = get_avatar_url($user->user_email, array('size' =>  16, 'default'   =>  'retro'));
        $avatar_big = get_avatar_url($user->user_email, array('size' =>  200, 'default'   =>  'retro'));
        $is_admin = user_can($user->ID, 'activate_plugins');
        $user_type = ($is_admin) ? 'Admin' : 'User';
        $emoji_type = ($is_admin) ? ':cop:' : ':ghost:';

        $attachments =  array(
            "color" => "#36a64f",
            "pretext" => sprintf(__('%s %s: `%s` Logged in successfully.', 'wpsl'), $emoji_type, $user_type, $user_login),
            "author_name" => $user_login,
            "author_link" => $link,
            "author_icon" => $avatar_small,
            "title" => __('New login', 'wpsl'),
            "title_link" => $url,
            "text" => sprintf(__('`%s` logged in successfully.', 'wpsl'), $user_login),
            "thumb_url" => $avatar_big,
            "fields" => array(
                array(
                    "title" => __('Username', 'wpsl'),
                    "value" => $user_login,
                    "short" => true
                ),
                array(
                    "title" => __('Date', 'wpsl'),
                    "value" => wp_date('Y-m-d H:i:s'),
                    "short" => true
                )
            ),
            "footer" => parent::footer(),
            "ts" => current_time('timestamp', 1)
        );

        do_action('wpsl_run', $attachments, $channel);
    }
}

new WPSL_Wp_Login();
