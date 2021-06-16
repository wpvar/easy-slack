<?php

/**
 * @package WPSL
 */
defined('ABSPATH') or die();

spl_autoload_register('wpsl_autoload');

/**
 * autoload function
 *
 * Loads classes.
 * 
 * @param string $className
 * 
 */
function wpsl_autoload($className)
{
    $path = plugin_dir_path(__FILE__);
    $class = $path . $className . '.class.php';
    if (file_exists($class) && !class_exists($className)) {
        require $class;
    }
}

new WPSL_Core();
new WPSL_Blocks();

if (is_admin()) {
    new WPSL_Options();
}
