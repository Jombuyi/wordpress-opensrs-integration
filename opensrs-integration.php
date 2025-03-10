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

/**
 * Registers the custom Gutenberg block for displaying the OpenSRS form.
 */
function opensrs_register_custom_block() {
    // Register the block editor script from the blocks directory
    wp_register_script(
        'opensrs-block-editor',
        OSRS_PLUGIN_URL . 'blocks/block.js',
        array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
        filemtime( OSRS_PLUGIN_DIR . 'blocks/block.js' )
    );

    // Register the dynamic block (rendered via PHP)
    register_block_type( 'opensrs/integration-form', array(
        'editor_script'   => 'opensrs-block-editor',
        'render_callback' => 'opensrs_render_block',
    ) );
}
add_action( 'init', 'opensrs_register_custom_block' );

/**
 * Render callback for the custom block.
 * Uses output buffering to capture the form output.
 */
function opensrs_render_block() {
    ob_start();
    if ( class_exists( 'Opensrs_Form' ) ) {
        Opensrs_Form::display_form();
    }
    return ob_get_clean();
}

