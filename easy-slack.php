<?php

/**
 * Plugin Name:       Easy Slack
 * Plugin URI:        https://github.com/wpvar/easy-slack
 * Description:       Integrate and monitor your WordPress with Slack
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      5.5
 * Author:            Easy Slack
 * Author URI:        https://github.com/wpvar/easy-slack/blob/master/README.md
 * License:           GNU Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wpsl
 * Domain Path:       /languages
 * @package WPSL
 */

defined('ABSPATH') or die();

define('WPSL_URL', plugin_dir_url(__FILE__));
define('WPSL_PATH', plugin_dir_path(__FILE__));
define('WPSL_BASE', plugin_basename(__FILE__));
define('WPSL_FILE', __FILE__);
define('WPSL_VERSION', '1.0.0');

/* Setting up WP shamsi */
require_once 'inc/autoload.php';