<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

class WPSL_Wp_Mail extends WPSL_Core
{
    function __construct()
    {
        global $wpsl_meta;

        $hook = 'wp_mail';
        $package = 'core';
        $title = __('Email send', 'wpsl');
        $desc = __('Send Slack notification when an email outgoing to a user. <b>This may content sensitive data, <span style="color: red;">DO NOT</span> enable on public workspaces</b>.', 'wpsl');

        $wpsl_meta[] = array(
            'hook'  =>  $hook,
            'package'   =>  $package,
            'title'   =>  $title,
            'desc'   =>  $desc,
            'default'   =>  'no',
            'order' =>  102
        );

        add_filter('wp_mail', array($this, 'wp_mail'), 10, 1);
    }

    public function wp_mail($args)
    {
        $url = get_home_url();

        $settings = parent::option('accordion', false, null);
        $active = (!empty($settings['wp_mail_switch']) && $settings['wp_mail_switch'] == 'yes') ? true : false;
        $channel = (empty($settings['wp_mail_channel'])) ? false : esc_attr($settings['wp_mail_channel']);

        if (!$active) {
            return;
        }

        $to = $args['to'];
        $subject = $args['subject'];
        $message = $args['message'];

        $attachments =  array(
            "color" => "#36a64f",
            "pretext" => sprintf(__(':email: Mail sent to `%s`.', 'wpsl'), $to),
            "author_name" => 'SYSTEM',
            "author_link" => $url,
            "title" => __('Mail Sent', 'wpsl'),
            "title_link" => $url,
            "text" => sprintf(__('Email `%s` sent to `%s`.', 'wpsl'), $subject, $to),
            "fields" => array(
                array(
                    "title" => __('To', 'wpsl'),
                    "value" => $to,
                    "short" => true
                ),
                array(
                    "title" => __('Date', 'wpsl'),
                    "value" => wp_date('Y-m-d H:i:s'),
                    "short" => true
                ),
                array(
                    "title" => __('Content', 'wpsl'),
                    "value" => strip_tags($message),
                    "short" => false
                )
            ),
            "footer" => parent::footer(),
            "ts" => current_time('timestamp', 1)
        );

        do_action('wpsl_run', $attachments, $channel);

        return $args;
    }
}

new WPSL_Wp_Mail();
