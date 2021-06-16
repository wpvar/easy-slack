<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

/**
 * Options class
 * 
 * @since 1.0.0
 * 
 */
class WPSL_Options extends WPSL_Core
{
    private $plugin_name;

    /**
     * Construct Options function
     * 
     * @since 1.0.0
     * 
     */
    function __construct()
    {
        if (!class_exists('Exopite_Simple_Options_Framework') && is_admin()) {
            require_once WPSL_PATH . 'lib/options/options-class.php';
        }
        add_action('init', array($this, 'settings'));
        $this->plugin_name = 'wpsl';
    }

    /**
     * Settings function
     * 
     * Register Easy Slack settings and options page.
     * 
     * @since 1.0.0
     *
     */
    public function settings()
    {
        $config_submenu = array(
            'type'              => 'menu',
            'id'                => $this->plugin_name,
            'parent'            => '',
            'submenu'           => false,
            'title'             => __('Easy Slack', 'wpsl'),
            'capability'        => 'manage_options',
            'plugin_basename'   =>  plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php'),
            'menu_title' => __('Easy Slack', 'wpsl'),
            'tabbed'            => true,
            'multilang'         => false,
            'icon' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1792 1792"><path d="M1583 776q62 0 103.5 40.5t41.5 101.5q0 97-93 130l-172 59 56 167q7 21 7 47 0 59-42 102t-101 43q-47 0-85.5-27t-53.5-72l-55-165-310 106 55 164q8 24 8 47 0 59-42 102t-102 43q-47 0-85-27t-53-72l-55-163-153 53q-29 9-50 9-61 0-101.5-40t-40.5-101q0-47 27.5-85t71.5-53l156-53-105-313-156 54q-26 8-48 8-60 0-101-40.5t-41-100.5q0-47 27.5-85t71.5-53l157-53-53-159q-8-24-8-47 0-60 42-102.5t102-42.5q47 0 85 27t53 72l54 160 310-105-54-160q-8-24-8-47 0-59 42.5-102t101.5-43q47 0 85.5 27.5t53.5 71.5l53 161 162-55q21-6 43-6 60 0 102.5 39.5t42.5 98.5q0 45-30 81.5t-74 51.5l-157 54 105 316 164-56q24-8 46-8zm-794 262l310-105-105-315-310 107z" fill="#fff"/></svg>')
        );

        $valid_channel = (is_array($this->channels()) ? true : $this->channels());

        if ($valid_channel === true) {
            $valid_channel =
                array(
                    'id'          => 'channel',
                    'type'        => 'select',
                    'title'       => __('Default slack channel', 'wpsl'),
                    'after'       => __('You can override default channel on "Actions" tab.', 'wpsl'),
                    'description' => __('Define your default Slack channel.', 'wpsl'),
                    'query'          => array(
                        'type'          => 'callback',
                        'function'      => array($this, 'channels'),
                    ),
                    'default'     => '#general',
                );
        } else {
            $valid_channel =
                array(
                    'id'          => 'channel',
                    'type'        => 'select',
                    'title'       => __('Default slack channel', 'wpsl'),
                    'before'        =>  parent::error($valid_channel),
                    'after'       => __('You can override default channel on "Actions" tab.', 'wpsl'),
                    'description' => __('Define your default Slack channel.', 'wpsl'),
                    'attribiutes'   => array(
                        'disabled'  =>  'disabled',
                    ),
                    'options'   =>  array()
                );
        }

        $fields[] = array(
            'name'   => 'general',
            'title'  => __('Settings', 'wpsl'),
            'icon'   => 'dashicons-admin-tools',
            'fields' => array(
                array(
                    'type' => 'content',
                    'title' => __('Connection', 'wpsl'),
                    'description' => __('Connction status to slack.com&#39;s API service.', 'wpsl'),
                    'after'       => __('Plugin will not work if connection status is not set as "Connected".', 'wpsl'),
                    'content' => parent::connection(),
                ),
                array(
                    'id'          => 'token',
                    'type'        => 'text',
                    'title'       => __('Bot User OAuth Token', 'wpsl'),
                    'after'       => __('Please visit <b>"Installation"</b> tab.', 'wpsl'),
                    'description' => __('You can generate Bot tokens after setting up an app. Please visit "Installation" tab for more informations.', 'wpsl'),
                    'attributes'    => array(
                        'placeholder' => __('xoxb-', 'wpsl'),
                        'required'   => 'required',
                    )
                ),
                $valid_channel,
            )
        );

        global $wpsl_meta;
        $metas = $wpsl_meta;
        usort($metas, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        $accordion = array();

        foreach ($metas as $meta) {
            $accordion[] =
                array(
                    'options' => array(
                        'icon'   => 'fa fa-star',
                        'title'  => '[' . strtoupper($meta['package']) . ']' . ' ' . $meta['title'],
                        'closed' => true,
                    ),
                    'fields' => array(
                        array(
                            'id'      => $meta['hook'] . '_content',
                            'type'    => 'content',
                            'content'   =>  $meta['desc']
                        ),
                        array(
                            'id'      => $meta['hook'] . '_switch',
                            'type'    => 'switcher',
                            'title'   => __('Enable', 'wpsl'),
                            'label'   => __('Enable / Disable', 'wpsl'),
                            'default' => $meta['default'],
                        ),
                        array(
                            'id'          => $meta['hook'] . '_channel',
                            'type'        => 'select',
                            'title'       => __('Slack channel', 'wpsl'),
                            'after'       => __('This will override default channel.', 'wpsl'),
                            'description' => __('Select Slack channel to post this action on.', 'wpsl'),
                            'query'          => array(
                                'type'          => 'callback',
                                'function'      => array($this, 'channels'),
                            ),
                            'default'     => '#general',
                        ),
                    ),
                );
        }

        $fields[] = array(
            'name'   => 'options',
            'title'  => __('Actions', 'wpsl'),
            'icon'   => 'dashicons-admin-generic',
            'fields' => array(
                array(
                    'id'          => 'accordion',
                    'type'        => 'accordion',
                    'title'       => __('Options', 'wpsl'),
                    'description'  =>  __('You can manage each action&#39;s settings here.', 'wpsl'),
                    'options' => array(
                        'allow_all_open' => false,
                    ),
                    'sections'  => $accordion,
                ),
            )
        );

        $fields[] = array(
            'name'   => 'permissions',
            'title'  => __('Permissions', 'wpsl'),
            'icon'   => 'dashicons-admin-network',
            'fields' => array(
                array(
                    'id'             => 'block-roles',
                    'type'           => 'select',
                    'title'          => esc_html__('Block Roles', 'wpsl'),
                    'description'          => esc_html__('Users with these roles will have access to insert or manage blocks generated by Easy Slack.', 'wpsl'),
                    'query'          => array(
                        'type'          => 'callback',
                        'function'      => array($this, 'roles'),
                        'args'          => array()
                    ),
                    'default'   =>  'administrator',
                    'attributes' => array(
                        'multiple' => 'multiple',
                        'style'    => 'width: 200px; height: 56px;',
                    ),
                    'class'       => 'chosen',
                ),
            )
        );

        $html = __('<h1>Installation Guide</h1>', 'wpsl');
        $html .= __('<p>Easy Slack requires an App and a token to connect WordPress to your Slack workspace. This guide will help you manage required steps to achieve this connection.</p>', 'wpsl');
        $html .= __('<h2>1. Create a new app</h2>', 'wpsl');
        $html .= __('<p>Login to your Slack acount and navigate to this page: <a href="https://api.slack.com/apps" target="_blank">api.slack.com/apps</a>.</p>', 'wpsl');
        $html .= __('<p>Click on <b>"Create New App"</b>.</p>', 'wpsl');
        $html .= $this->install_img('step1');
        $html .= __('<p>Next, select <b>"From scratch"</b>.</p>', 'wpsl');
        $html .= $this->install_img('step2');
        $html .= __('<p>Now choose an app name and your workspace you want to install app on.</p>', 'wpsl');
        $html .= $this->install_img('step3');
        $html .= __('<h2>2. Set scopes and permissions</h2>', 'wpsl');
        $html .= __('<p>Go to <b>"OAuth & Permissions"</b>.</p>', 'wpsl');
        $html .= $this->install_img('step4');
        $html .= __('<p>On <b>"Bot Token Scopes"</b> section, add following scopes: (You should add all of required scopes listed below, otherwise plugin may not work properly).</p>', 'wpsl');
        $html .= __('
                    <ul>
                        <li>chat:write</li>
                        <li>chat:write.public</li>
                        <li>channels:read</li>
                        <li>groups:read</li>
                        <li>im:read</li>
                        <li>mpim:read</li>
                        <li>channels:history</li>
                        <li>groups:history</li>
                        <li>im:history</li>
                        <li>mpim:history</li>
                    </ul>', 'wpsl');
        $html .= $this->install_img('step5');
        $html .= __('<h2>3. Install App on workspace</h2>', 'wpsl');
        $html .= __('<p>After setting up scopes, navigate to top of the page and click on <b>"Install to Workspace"</b>. You will be redirected to authentication page, allow scopes by clicking on <b>"Allow"</b>.</b></p>', 'wpsl');
        $html .= $this->install_img('step6');
        $html .= __('<h2>4. Copy Token</h2>', 'wpsl');
        $html .= __('<p>Now you will be redirected to App panel where you can see your token. Copy this token and paste it in Easy Slack&#39;s settings page (<b>"Bot User OAuth Token"</b> field).</p>', 'wpsl');
        $html .= $this->install_img('step7');
        $html .= __('<h2>5. Invite app to channels</h2>', 'wpsl');
        $html .= __('<p>In order to use Slack Message Block, you should invite your app to channels using this command: (Otherwise you will not be able to display Slack messages on your WordPress). </p>', 'wpsl');
        $html .= __('<p><b>/invite @YourAppName</b></p>', 'wpsl');
        $html .= __('<p>change @YourAppName to your App&#39;s name.</p>', 'wpsl');
        $html .= $this->install_img('invite');
        $html .= __('<p></p>', 'wpsl');

        $fields[] = array(
            'name'   => 'installation',
            'title'  => __('Installation', 'wpsl'),
            'icon'   => 'dashicons-info-outline',
            'fields' => array(
                array(
                    'type'    => 'content',
                    'class' =>  'wp-slack_installation',
                    'content' => $html,
                ),
            )
        );

        $options_panel = new Exopite_Simple_Options_Framework($config_submenu, $fields);
    }

    /**
     * Roles function
     *
     * Get WordPress roles.
     * 
     * @since 1.0.0
     * 
     * @return array Array of roles.
     */
    public function roles()
    {
        $all_roles = (array) wp_roles()->roles;

        $roles = array();

        foreach ($all_roles as $key => $value) {
            $roles[esc_html($key)] = esc_html($value['name']);
        }

        return $roles;
    }

    /**
     * Channels function
     *
     * Get available Channels on Slack.
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public function channels()
    {
        if (!parent::is_settings()) {
            return;
        }

        $cache = get_transient('wpsl-slack-channels');

        if ($cache != false) {
            return $cache;
        }

        $channels = array();
        $arrs = parent::channels();

        if (!is_array($arrs)) {
            return $arrs;
        }

        foreach ($arrs as $arr) {
            $channels['#' . $arr['name']] = '#' . $arr['name'];
        }

        set_transient('wpsl-slack-channels', $channels, 5);


        return $channels;
    }

    /**
     * Installation image function
     * 
     * Generate Installation images.
     * 
     * @since 1.0.0
     *
     * @param string $name
     * @param string $format
     * @return string img tag
     */
    private function install_img($name = null, $format = 'png')
    {
        $link = WPSL_URL . 'assets/img/installation/' . $name . '.' . $format;
        $image = sprintf('<div class="wp-slack_install_img_cnt"><img class="wp-slack_install_img" src="%s" loading="lazy" alt="%s" /></div>', $link, __('Installation Guide', 'wpsl'));

        return $image;
    }
}
