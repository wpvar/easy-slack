<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

/**
 * Blocks class
 * 
 * @since 1.0.0
 * 
 */
class WPSL_Blocks
{
    /**
     * Construct function
     * 
     * Construct Blocks class
     * 
     * @since 1.0.0
     * 
     */
    function __construct()
    {
        add_action('init', array($this, 'wpsl_blocks_slack'));
        add_filter('block_categories', array($this, 'wpsl_block_category'));
    }


    /**
     * Register Blocks function
     * 
     * Register Gutenber Blocks
     * 
     * @since 1.0.0
     *
     */
    public function wpsl_blocks_slack()
    {
        if (function_exists('register_block_type_from_metadata')) {
            register_block_type_from_metadata(WPSL_PATH . 'blocks/slack', array('render_callback' => array($this, 'wpsl_blocks_slack_render_callback')));
        }
    }

    /**
     * Callback function
     *
     * Callback Block render.
     * 
     * @since 1.0.0
     * 
     * @param array $block_attributes
     * @return string Block content;
     */
    public function wpsl_blocks_slack_render_callback($block_attributes)
    {
        $link = (!empty($block_attributes['slack'])) ? $block_attributes['slack'] : '';

        if ($link == '') {
            return __('Please enter the URL', 'wpsl');
        }
    
        if (!mb_strpos($link, 'slack.com/archives/')) {
            return __('URL is not valid, Please try again.', 'wpsl');
        }
    
        $id = explode('/', esc_attr($link));
        $channel = $id[4];
        $ts = $id[5];
    
        $slack = new WPSL_Core;
        $slack = $slack->message($channel, $ts);
        $text = $slack['messages'][0]['text'];
        $attach_text = $slack['messages'][0]['attachments'][0]['text'];
    
        $parse = new WPSL_Parser();
    
        $wpsl_roles = new WPSL_Core;
        $wpsl_roles = $wpsl_roles->option('block-roles', false, 'administrator');
    
        ob_start();
        if(!empty($slack['error']) && $slack['error'] == 1) {
            $html = $slack['content'];
        }
        elseif(empty(array_intersect($wpsl_roles, (array) wp_get_current_user()->roles)) && $wpsl_roles != 'administrator') {
            $html = __('You dont have permission to add messages from Slack.', 'wpsl');
        }
        elseif (!empty($text)) {
            $html = $parse->parser(wp_kses($text, wp_kses_allowed_html()), $link);
        } elseif (!empty($attach_text)) {
            $html = $parse->parser(wp_kses($attach_text, wp_kses_allowed_html()), $link);
        } else {
            $html = __('There is no text defined for this message.', 'wpsl');
        }
    
        echo '<div class="wp-slack_response wp-slack_' . $block_attributes['alignment'] . '">' . $html . '</div>';
    
        return ob_get_clean();
    }


    /**
     * Category function
     *
     * Register new Block category.
     * 
     * @since 1.0.0
     * 
     * @param array $categories
     */
    public function wpsl_block_category($categories)
    {
        $category_slugs = wp_list_pluck($categories, 'slug');
        return in_array('wpsl', $category_slugs, true) ? $categories : array_merge(
            $categories,
            array(
                array(
                    'slug'  => 'wpsl',
                    'title' => __('Easy Slack', 'wpsl'),
                    'icon'  => null,
                ),
            )
        );
    }
}
