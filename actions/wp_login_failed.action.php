<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

class WPSL_Wp_Login_Failed extends WPSL_Core
{
    function __construct()
    {
        global $wpsl_meta;

        $hook = 'wp_login_failed';
        $package = 'core';
        $title = __('User login failed', 'wpsl');
        $desc = __('Send Slack notification when there is a failed login attempt.', 'wpsl');

        $wpsl_meta[] = array(
            'hook'  =>  $hook,
            'package'   =>  $package,
            'title'   =>  $title,
            'desc'   =>  $desc,
            'default'   =>  'yes',
            'order' => 101
        );

        add_action('wp_login_failed', array($this, 'wp_login_failed'), 10, 2);
    }

    public function wp_login_failed($user_login, $error)
    {
        $url = get_home_url();

        $settings = parent::option('accordion', false, null);
        $active = (empty($settings['wp_login_failed_switch']) || $settings['wp_login_failed_switch'] == 'yes') ? true : false;
        $channel = (empty($settings['wp_login_failed_channel'])) ? false : esc_attr($settings['wp_login_failed_channel']);

        if (!$active) {
            return;
        }

        $attachments =  array(
            "color" => "#d63638",
            "pretext" => sprintf(__(':no_entry_sign: User `%s` failed to login.', 'wpsl'), $user_login),
            "author_name" => $user_login,
            "title" => __('Failed login', 'wpsl'),
            "title_link" => $url,
            "text" => sprintf(__('`%s` failed to login. Take action if this is a suspicious attempt.', 'wpsl'), $user_login),
            "fields" => array(
                array(
                    "title" => __('Error detail', 'wpsl'),
                    "value" => strip_tags($error->get_error_message()),
                    "short" => false
                ),
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

new WPSL_Wp_Login_Failed();
