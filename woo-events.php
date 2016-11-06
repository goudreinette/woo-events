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

new Admin;
new Display;
Meta::updateExpired();

/**
 * TODO:
 * - Cleanup Display
 * - External link
 * - Expiration
 */