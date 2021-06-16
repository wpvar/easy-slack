<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

class WPSL_Wp_Error_Added extends WPSL_Core
{
    function __construct()
    {
        global $wpsl_meta;

        $hook = 'wp_error_added';
        $package = 'core';
        $title = __('WordPress errors', 'wpsl');
        $desc = __('Send Slack notification when an error occurs on WordPress. WordPress 5.6.0 or higher required. <b>Enable only for debug purposes. This may content highly sensitive data, <span style="color: red;">DO NOT</span> enable on public workspaces</b>.', 'wpsl');

        $wpsl_meta[] = array(
            'hook'  =>  $hook,
            'package'   =>  $package,
            'title'   =>  $title,
            'desc'   =>  $desc,
            'default'   =>  'no',
            'order' => 102
        );

        add_action('wp_error_added', array($this, 'wp_error_added'), 1, 4);
    }

    public function wp_error_added($code, $message, $data, $wp_error)
    {
        $ignore = array(
            'empty_username',
            'empty_password',
            'expired',
            'loggedout',
            'email_sent_already',
            'registered',
            'confirm'
        );

        if(in_array($code, $ignore)) {
            return;
        }

        $url = get_home_url();

        $settings = parent::option('accordion', false, null);
        $active = (!empty($settings['wp_error_added_switch']) && $settings['wp_error_added_switch'] == 'yes') ? true : false;
        $channel = (empty($settings['wp_error_added_channel'])) ? false : esc_attr($settings['wp_error_added_channel']);

        if (!$active) {
            return;
        }

        $attachments =  array(
            "color" => "#d63638",
            "pretext" => sprintf(__(':sos: Error `%s` detected, Please check it.', 'wpsl'), $code),
            "author_name" => 'SYSTEM',
            "title" => __('Error triggered', 'wpsl'),
            "title_link" => $url,
            "text" => sprintf(__('Error `%s` detected on our website, This may cause downtime.', 'wpsl'), $code),
            "fields" => array(
                array(
                    "title" => __('Error code', 'wpsl'),
                    "value" => $code,
                    "short" => true
                ),
                array(
                    "title" => __('Date', 'wpsl'),
                    "value" => wp_date('Y-m-d H:i:s'),
                    "short" => true
                ),
                array(
                    "title" => __('Error message', 'wpsl'),
                    "value" => strip_tags($message),
                    "short" => false
                ),
            ),
            "footer" => parent::footer(),
            "ts" => current_time('timestamp', 1)
        );

        do_action('wpsl_run', $attachments, $channel);
    }
}

new WPSL_Wp_Error_Added();
