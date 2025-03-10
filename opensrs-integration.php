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


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'OSRS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OSRS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files
require_once OSRS_PLUGIN_DIR . 'includes/class-opensrs-integration.php';
require_once OSRS_PLUGIN_DIR . 'includes/class-opensrs-api.php';
require_once OSRS_PLUGIN_DIR . 'includes/class-opensrs-form.php';

// Initialize the plugin
$opensrs_integration = Opensrs_Integration::get_instance();

// Register activation hook if needed
register_activation_hook( __FILE__, array( 'Opensrs_Integration', 'activate' ) );
