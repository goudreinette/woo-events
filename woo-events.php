<?php namespace WooEvents;


/*
Plugin Name: WooEvents
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: .
Version: 1.0
Author: reinvdwoerd
Author URI: reinvdwoerd.herokuapp.com
License: A "Slug" license name e.g. GPL2
Text Domain: woo-events
*/


require "vendor/autoload.php";

$assetsDir = plugin_dir_url(__FILE__);

/**
 * Initialize
 */
use Utils\View;

global $view;
$view = new View($assetsDir);

new Admin($view);
new Display($view);
new Shortcode($view);


/**
 * Update expired events
 */
add_action('init', function () {
    Model::updateExpired();
});

/**
 * Translations
 */
add_action('plugins_loaded', function () {
    load_plugin_textdomain('woo-events', false, dirname(plugin_basename(__FILE__)));
});
