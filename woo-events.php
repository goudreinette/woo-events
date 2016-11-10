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
/**
 * Initialize template engine
 */
$mustache = new \Mustache_Engine([
    'loader' => new \Mustache_Loader_FilesystemLoader(plugin_dir_path(__FILE__) . '/templates')
]);

new Admin($mustache);
new Display($mustache);

/**
 * Update expired events
 */
Meta::updateExpired();

/**
 * TODO:
 * - Expiration
 */