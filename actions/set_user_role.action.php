<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

class WPSL_Set_User_Role extends WPSL_Core
{
    function __construct()
    {
        global $wpsl_meta;

        $hook = 'set_user_role';
        $package = 'core';
        $title = __('User Role Change', 'wpsl');
        $desc = __('Send Slack notification when user role changes.', 'wpsl');

        $wpsl_meta[] = array(
            'hook'  =>  $hook,
            'package'   =>  $package,
            'title'   =>  $title,
            'desc'   =>  $desc,
            'default'   =>  'yes',
            'order' =>  101
        );

        add_action('set_user_role', array($this, 'set_user_role'), 10, 3);
    }

    public function set_user_role($user_id, $role, $old_roles)
    {
        $url = get_home_url();

        $settings = parent::option('accordion', false, null);
        $active = (empty($settings['set_user_role_switch']) || $settings['set_user_role_switch'] == 'yes') ? true : false;
        $channel = (empty($settings['set_user_role_channel'])) ? false : esc_attr($settings['set_user_role_channel']);

        if (!$active) {
            return;
        }

        $user = get_userdata($user_id );
        $user_login = $user->user_login;
        $link = get_edit_profile_url($user->ID);
        $avatar_small = get_avatar_url($user->user_email, array('size' =>  16, 'default'   =>  'retro'));
        $avatar_big = get_avatar_url($user->user_email, array('size' =>  200, 'default'   =>  'retro'));
        $is_admin = user_can($user_id, 'activate_plugins');
        $user_type = ($is_admin) ? 'Admin' : 'User';
        $emoji_type = ($is_admin) ? ':rotating_light:' : ':vertical_traffic_light:';

        $old = '';
        foreach($old_roles as $old_role) {
            $old .= $old_role;
        }

        $attachments =  array(
            "color" => "#36a64f",
            "pretext" => sprintf(__('%s Role for %s `%s` has changed to `%s`.', 'wpsl'), $emoji_type, $user_type, $user_login, $role),
            "author_name" => 'SYSTEM',
            "author_link" => $link,
            "author_icon" => $avatar_small,
            "title" => __('Role change', 'wpsl'),
            "title_link" => $url,
            "text" => sprintf(__('Role for user `%s` has changed :sound:', 'wpsl'), $user_login),
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
                ),
                array(
                    "title" => __('New Role', 'wpsl'),
                    "value" => $role,
                    "short" => true
                ),
                array(
                    "title" => __('Old Role', 'wpsl'),
                    "value" => $old,
                    "short" => true
                )
            ),
            "footer" => parent::footer(),
            "ts" => current_time('timestamp', 1)
        );

        do_action('wpsl_run', $attachments, $channel);
    }
}

new WPSL_Set_User_Role();
