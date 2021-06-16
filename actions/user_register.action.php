<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

class WPSL_User_Register extends WPSL_Core
{
    function __construct()
    {
        global $wpsl_meta;

        $hook = 'user_register';
        $package = 'core';
        $title = __('New user registration', 'wpsl');
        $desc = __('Send Slack notification when a new user registers.', 'wpsl');

        $wpsl_meta[] = array(
            'hook'  =>  $hook,
            'package'   =>  $package,
            'title'   =>  $title,
            'desc'   =>  $desc,
            'default'   =>  'yes',
            'order' =>  100
        );

        add_action('user_register', array($this, 'user_register'), 10, 2);
    }

    public function user_register($id)
    {
        $url = get_home_url();

        $settings = parent::option('accordion', false, null);
        $active = (empty($settings['user_register_switch']) || $settings['user_register_switch'] == 'yes') ? true : false;
        $channel = (empty($settings['user_register_channel'])) ? false : esc_attr($settings['user_register_channel']);

        if (!$active) {
            return;
        }

        $user = get_userdata($id);
        $email = $user->user_email;

        $attachments =  array(
            "color" => "#36a64f",
            "pretext" => sprintf(__(':mega: New user registered: `%s`', 'wpsl'), $email),
            "author_name" => 'SYSTEM',
            "author_link" => get_edit_profile_url($user->ID),
            "author_icon" => get_avatar_url($email, array('size' =>  16, 'default'   =>  'retro')),
            "title" => __('New registration', 'wpsl'),
            "title_link" => $url,
            "text" => sprintf(__('We have a new user on our site: `%s` :tada:', 'wpsl'), $email),
            "thumb_url" => get_avatar_url($email, array('size' =>  200, 'default'   =>  'retro')),
            "fields" => array(
                array(
                    "title" => __('Email', 'wpsl'),
                    "value" => $email,
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

new WPSL_User_Register();
