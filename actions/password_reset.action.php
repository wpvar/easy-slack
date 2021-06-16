<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

class WPSL_Password_Reset extends WPSL_Core
{
    function __construct()
    {
        global $wpsl_meta;

        $hook = 'password_reset';
        $package = 'core';
        $title = __('Password reset', 'wpsl');
        $desc = __('Send Slack notification when a user resets password.', 'wpsl');

        $wpsl_meta[] = array(
            'hook'  =>  $hook,
            'package'   =>  $package,
            'title'   =>  $title,
            'desc'   =>  $desc,
            'default'   =>  'yes',
            'order' =>  100
        );

        add_action('password_reset', array($this, 'password_reset'), 10, 2);
    }

    public function password_reset($user, $pass)
    {
        $url = get_home_url();

        $settings = parent::option('accordion', false, null);
        $active = (empty($settings['password_reset_switch']) || $settings['password_reset_switch'] == 'yes') ? true : false;
        $channel = (empty($settings['password_reset_channel'])) ? false : esc_attr($settings['password_reset_channel']);

        if (!$active) {
            return;
        }

        $user_login = $user->user_login;

        $attachments =  array(
            "color" => "#36a64f",
            "pretext" => sprintf(__(':key: Password for user: `%s` has been reset successfully.', 'wpsl'), $user_login),
            "author_name" => $user_login,
            "author_link" => get_edit_profile_url($user->ID),
            "author_icon" => get_avatar_url($user->user_email, array('size' =>  16, 'default'   =>  'retro')),
            "title" => __('Password reset', 'wpsl'),
            "title_link" => $url,
            "text" => sprintf(__('Password for `%s` has been reset successfully. :bell:', 'wpsl'), $user_login),
            "thumb_url" => get_avatar_url($user->user_email, array('size' =>  200, 'default'   =>  'retro')),
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

new WPSL_Password_Reset();
