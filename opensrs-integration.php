<?php
/* opensrs-integration.php - Main Plugin File */
/**
* Plugin Name: OpenSRS SSL Reseller Integration
* Plugin URI:  https://letustech.co.za
* Description: A WordPress plugin that integrates the OpenSRS API to manage domain registrations and related services.
* Version:     1.0.0
* Author:      Jonathan Tshitenda
* License:     GPL2 or later
* Text Domain: opensrs-integration
*
* @package OpenSRS_Integration
*/


if (!defined('ABSPATH')) exit;

// Define plugin constants
define('OPENSRS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('OPENSRS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load required files
require_once OPENSRS_PLUGIN_PATH . 'includes/class-opensrs-integration.php';
require_once OPENSRS_PLUGIN_PATH . 'includes/class-opensrs-api.php';
require_once OPENSRS_PLUGIN_PATH . 'includes/class-opensrs-form.php';

// Initialize the plugin
add_action('plugins_loaded', function() {
    OpenSRS_Integration::instance();
});

// Add settings link
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=opensrs-settings') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
});