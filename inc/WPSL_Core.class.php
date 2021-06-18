<?php

/**
 * @package WPSL
 */

defined('ABSPATH') or die();

/**
 * Core class
 * 
 * @since 1.0.0
 * 
 */
class WPSL_Core
{
    protected $wpsl_pro;

    /**
     * Construct function
     * 
     * Construct class.
     * 
     * @since 1.0.0
     */
    function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'scripts'));

        global $wpsl_meta;
        $wpsl_meta = array();
        foreach (glob(WPSL_PATH . 'actions/*.action.php') as $filename) {
            include_once $filename;
        }

        $pro = (defined('WPSL_PRO')) ? $this->wpsl_pro = true : false;

        if ($pro) {
            do_action('wpsl_custom_actions');
        }

        add_action('wpsl_run', array($this, 'slack'), 10, 2);
        add_action('admin_head', array($this, 'remove_notices'), 10);
    }

    /**
     * Connection function
     *
     * Checks connection status.
     * 
     * @since 1.0.0
     * 
     * @return string Connection status.
     */
    public function connection()
    {

        $page = (isset($_GET['page']) && $_GET['page'] == 'wpsl' && is_admin()) ? true : false;
        $refresh = '<span class="wpsl-refresh"><a href="' . get_admin_url(null, 'admin.php?page=wpsl') . '">[Refresh]</a></span>';

        if (!$page) {
            return null;
        }

        $token = $this->token();

        if (!$token) {
            return $this->error('Not installed', 'wpsl-inline') . $refresh;
        }

        require_once(WPSL_PATH . 'lib/slack/vendor/autoload.php');
        $slack = new wrapi\slack\slack($token);
        $connection = $slack->api->test();

        if ($connection['ok'] == 1) {
            return $this->success('Connected', 'wpsl-inline') . $refresh;
        } else {
            return $this->error('Disconnected', 'wpsl-inline') . $refresh;
        }
    }

    /**
     * Slack function
     *
     * Send message to Slack.
     * 
     * @since 1.0.0
     * 
     * @param boolean $attachments
     * @param boolean $channel
     * @return bool False on error.  
     */
    public function slack($attachments = false, $channel = false)
    {

        if (extension_loaded('zlib')) {
            ob_start("ob_gzhandler");
        }

        $token = $this->token();

        if (!$attachments) {
            $attachments = '';
        } else {
            $attachments = array($attachments);
        }

        if (!$token) {
            return false;
        }

        if (!$channel) {
            $channel = $this->default_channel();
        }

        @require_once(WPSL_PATH . 'lib/slack/vendor/autoload.php');
        $slack = @new wrapi\slack\slack($token);
        @$slack->chat->postMessage(
            array(
                "channel" => $channel,
                "attachments"   => json_encode($attachments, JSON_UNESCAPED_UNICODE)
            )
        );
    }

    /**
     * Channels function
     *
     * List workspace channels
     * 
     * @since 1.0.0
     * 
     * @return mixed String on Error, array of channels on success.
     */
    public function channels()
    {

        $token = $this->token();

        if (!$token) {
            return false;
        }

        require_once(WPSL_PATH . 'lib/slack/vendor/autoload.php');
        $slack = new wrapi\slack\slack($token);
        $channels = $slack->conversations->list(array("exclude_archived" => 1));

        if (!empty($channels['error'])) {
            return sprintf(__('An error occured: %s %s', 'wpsl'), $channels['error'], (!empty($channels['needed']) ? $channels['needed'] : ''));
        } else {
            return $channels['channels'];
        }
    }

    /**
     * Message function
     *
     * Retrieve Message from slack
     * 
     * @since 1.0.0
     * 
     * @param string $channel
     * @param string $ts
     * @return string Display message from Slack.
     */
    public function message($channel, $ts)
    {
        $ts = str_replace('p', '', $ts);
        $ts = substr_replace($ts, '.', -6, 0);

        $token = $this->token();

        if (!$token) {
            $reply = array(
                'error' =>  1,
                'content'   =>  __('Easy Slack is not connected to your Slack workspace. Please read Installation Guide on plugin&#39;s settings.', 'wpsl')
            );
            return $reply;
        }

        require_once(WPSL_PATH . 'lib/slack/vendor/autoload.php');
        $slack = new wrapi\slack\slack($token);
        $message = $slack->conversations->history(array("channel" => $channel, "latest"  =>  $ts, "limit" =>  "1", "inclusive" =>  true));

        if (!empty($message['error'])) {
            if ($message['error'] == 'not_in_channel') {
                $reply = array(
                    'error' =>  1,
                    'content'   =>  __('Slack App connected to Easy Slack plugin is not invited on this channel. Invite it on Slack using this command: /invite @YourAppName', 'wpsl')
                );
            } else {
                $reply = array(
                    'error' =>  1,
                    'content'   =>  sprintf(__('An error occured: %s %s', 'wpsl'), $message['error'], (!empty($message['needed']) ? $message['needed'] : ''))
                );
            }
            return $reply;
        } else {
            return $message;
        }
    }

    /**
     * Default channel function
     *
     * Get default channel name.
     * 
     * @since 1.0.0
     * 
     * @return string Channel name.
     */
    protected function default_channel()
    {
        $channel = $this->option('channel', false, '#general');
        return $channel;
    }

    protected function token()
    {
        $token = $this->option('token', false, false);
        return $token;
    }

    public function option($option, $bool = false, $default = null)
    {
        $options = get_option('wpsl');
        $valid = (!empty($options[$option])) ? true : false;

        if (!$valid) {
            return $default;
        }

        if ($bool === true) {
            if ($options[$option] == 'yes') :
                return true;
            else :
                return false;
            endif;
        } else {
            return $options[$option];
        }
    }

    /**
     * Error function
     *
     * Print error message.
     * 
     * @since 1.0.0
     * 
     * @param string $text
     * @param string $class
     * @return string Error message.
     */
    public function error($text, $class = '')
    {
        return '<p class="wpsl-error ' . $class . '">' . __(ucfirst($text), 'wpsl') . '</p>';
    }

    /**
     * Success function
     *
     * Print success message.
     * 
     * @since 1.0.0
     * 
     * @param string $text
     * @param string $class
     * @return string Success message.
     */
    public function success($text, $class = '')
    {
        return '<p class="wpsl-success ' . $class . '">' . __(ucfirst($text), 'wpsl') . '</p>';
    }

    /**
     * Scripts function
     *
     * Enquee scripts
     * 
     * @since 1.0.0
     * 
     */
    public function scripts()
    {
        $page = (isset($_GET['page']) && $_GET['page'] == 'wpsl' && is_admin()) ? true : false;

        if ($page) {
            wp_enqueue_style('wpsl-style', WPSL_URL . 'assets/css/admin.css', array(), WPSL_VERSION);
        }
    }

    /**
     * Footer function
     *
     * Footer displayed on Slack.
     * 
     * @since 1.0.0
     * 
     * @return string Footer content.
     */
    protected function footer()
    {
        return "<https://wordpress.org/plugins/easy slack/|Generated by Easy Slack plugin>";
    }

    /**
     * Is on settings function
     *
     * Check if current page is settings page of Easy Slack
     * 
     * @since 1.0.0
     * 
     * @return boolean
     */
    public function is_settings()
    {
        $page = (isset($_GET['page']) && $_GET['page'] == 'wpsl' && is_admin()) ? true : false;

        return $page;
    }

    /**
     * Remove notices function
     *
     * Do not display notices inside plugins settings.
     *
     * @since 1.0.0
     *
     */
    public function remove_notices()
    {
        $page = isset($_GET['page']) ? sanitize_title($_GET['page']) : false;

        if ($page !== false && $page === 'wpsl') {
            remove_all_actions('admin_notices');
        }
    }
}
