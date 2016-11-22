<?php namespace WooEvents;

/*
Plugin Name: Woo Events
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: .
Version: 1.0
Author: reinvdwoerd
Author URI: reinvdwoerd.herokuapp.com
License: A "Slug" license name e.g. GPL2
*/


require "vendor/autoload.php";
require "src/CalendarWidget.php";

$assetsDir = plugin_dir_url(__FILE__);

/**
 * Initialize
 */
global $view;
$view = new View($assetsDir);

new Admin($view);
new Display($view);
new Shortcode($view);

/**
 * TODO:
 *  *Form van Calendar
 *  *First Event Border
 *  *List padding & Width
 *  *h5 a Style
 *  *Fix VC multiselect
 *  Testen
 *  * Add to cart
 *  * Optie om price en add to cart te hiden
 *  * Externe link
 * * Multiple Categories
 *  Subtitle
 *  End Date
 *  * Time
 */

/**
 * Test checklist
 * * Order
 * * Color
 * * Layout
 *   Expired
 */