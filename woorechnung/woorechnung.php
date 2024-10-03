<?php

/**
 * Plugin Name: Faktur Pro
 * Plugin URI: https://www.faktur.pro/
 * Description: Adds invoice creation functionality to WooCommerce.
 * Version: 3.1.5
 * Author: Zweischneider GmbH & Co. KG
 * Author URI: https://www.zweischneider.de
 *
 * Text Domain: fakturpro
 * Domain Path: /languages
 *
 * Requires at least: 3.0.0
 * Tested up to: 6.6.2
 * Tested PHP up to: 8.3
 * WC requires at least: 3.0.0
 * WC tested up to: 9.3.3
 *
 * @package FakturPro
 * @category Core
 * @author ZWEISCHNEIDER
 */

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) ) {
    exit;
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_block_editor', __FILE__, true );
	}
} );

// Get the name of this file in filsystem
// Get the root path of this plugin in filesystem
// Get the base URL of this plugin on the server
$plugin_file = __FILE__;
$plugin_path = plugin_dir_path( $plugin_file );
$plugin_url = plugins_url( '', $plugin_file );

$custom_config_path = $plugin_path . 'config-path.php';
$plugin_config = $plugin_path . 'config/config.php';

// Require all classes the plugin consists of
// Include the configuration file for the plugin
require_once (
    file_exists( $custom_config_path )
    && !empty( include( $custom_config_path ) )
    && file_exists( include( $custom_config_path ) )
    ? include( $custom_config_path )
    : $plugin_config
);
require_once ( $plugin_path . 'includes.php' );

// Create a new instance of the plugin class
// Run the plugin and register all hooks within WordPress
$plugin = new FP_Plugin( $plugin_file, $plugin_path, $plugin_url );
$plugin->run_plugin();

// Make the plugin instance global
$GLOBALS['fakturpro'] = $plugin;

/**
 * Faktur pro global plugin function.
 * 
 * @return FP_Plugin
 */
function fakturpro() {
    return $GLOBALS['fakturpro'];
}
