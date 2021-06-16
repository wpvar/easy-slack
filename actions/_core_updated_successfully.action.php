<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

class WPSL_Core_Update_Success extends WPSL_Core
{
    function __construct()
    {
        global $wpsl_meta;

        $hook = 'core_update_success';
        $package = 'core';
        $title = __('Core update success', 'wpsl');
        $desc = __('Send Slack notification when WordPress core updates successfully.', 'wpsl');

        $wpsl_meta[] = array(
            'hook'  =>  $hook,
            'package'   =>  $package,
            'title'   =>  $title,
            'desc'   =>  $desc,
            'default'   =>  'yes',
            'order' =>  101
        );

        add_action('_core_updated_successfully', array($this, 'core_update_success'), 10, 1);
    }

    public function core_update_success($version)
    {
        $url = get_home_url();

        $settings = parent::option('accordion', false, null);
        $active = (empty($settings['core_update_success_switch']) || $settings['core_update_success_switch'] == 'yes') ? true : false;
        $channel = (empty($settings['core_update_success_channel'])) ? false : esc_attr($settings['core_update_success_channel']);

        if (!$active) {
            return;
        }

        $attachments =  array(
            "color" => "#36a64f",
            "pretext" => sprintf(__(':white_check_mark: WordPress on `%s` updated successfully to version `%s`.', 'wpsl'), $url, $version),
            "author_name" => 'SYSTEM',
            "author_link" => $url,
            "title" => __('Core update success', 'wpsl'),
            "title_link" => $url,
            "text" => sprintf(__('WordPress updated to version `%s` :tada:', 'wpsl'), $version),
            "fields" => array(
                array(
                    "title" => __('Version', 'wpsl'),
                    "value" => $version,
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

new WPSL_Core_Update_Success();
