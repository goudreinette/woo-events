<?php namespace WooEvents;

/*
Plugin Name: WooEvents
Plugin URI: https://github.com/reinvdwoerd/woo-events
Description: .
Version: 1.0
Author: reinvdwoerd
Author URI: reinvdwoerd.herokuapp.com
License: A "Slug" license name e.g. GPL2
Text Domain: woo-events
*/

echo "<h1>Hello, Hans!</h1>";

require "vendor/autoload.php";
require "source/CalendarWidget.php";

use Utils\PluginContext;

class WooEvents extends PluginContext
{
    public $base = 'woo-events';
}

new WooEvents();
