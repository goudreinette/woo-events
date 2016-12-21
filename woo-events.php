<?php namespace WooEvents;

/*
Plugin Name: WooEvents
Plugin URI:  https://github.com/reinvdwoerd/woo-events
Description: .
Version: 1.0
Author: reinvdwoerd
Author URI: reinvdwoerd.herokuapp.com
License: A "Slug" license name e.g. GPL2
Text Domain: woo-events
*/


require "vendor/autoload.php";
require "source/CalendarWidget.php";

$assetsDir = plugin_dir_url(__FILE__);

echo "<h1>Hello, World!</h1>";

/**
 * Initialize
 */
use Utils\View;

global $view;
$view = new View($assetsDir);

new Admin($view);
new Display($view);
new EventList($view);


add_action('init', function () {

});

/**
 * Translations
 */
add_action('plugins_loaded', function () {
    load_plugin_textdomain('woo-events', false, dirname(plugin_basename(__FILE__)));
});
