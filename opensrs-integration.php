<?php
/**
* Plugin Name: OpenSRS SSL Reseller Integration
* Plugin URI:  https://letustech.co.za
* Description: A WordPress plugin that integrates the OpenSRS API to manage domain registrations and related services.
* Version:     1.0.0
* Author:      Jonathan Tshitenda
* License:     GPL2
* Text Domain: opensrs-integration
*
* @package OpenSRS_Integration
*/

/* This file defines a WordPress plugin that integrates the OpenSRS API to manage domain registrations and related services within a WordPress site. */


// Exit if accessed directly. 
if (!defined('ABSPATH')) {  // Security check to prevent direct access
    exit;
}


// Include necessary classes
require_once plugin_dir_path(__FILE__) . '/includes/class-opensrs-api.php'; // Absolute path to the plugin directory
require_once plugin_dir_path(__FILE__) . '/includes/class-opensrs.php'; // URL to the plugin directory

// Initialize the plugin
add_action('plugins_loaded', function() {
    new OpenSRS_Integration();
});