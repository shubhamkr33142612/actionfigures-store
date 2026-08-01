<?php
/**
 * Plugin Name:       Collectors Display Core
 * Plugin URI:        https://actionfigures.local
 * Description:       Core business logic for the collectible display store. Owns product taxonomies, order handling, and product personalization so the theme stays a pure presentation layer.
 * Version:           0.1.1
 * Author:            Store Team
 * Text Domain:       collectors-display-core
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * WC requires at least: 9.0
 * WC tested up to:      10.9
 *
 * @package CollectorsDisplayCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CDC_VERSION', '0.1.1' );
define( 'CDC_PLUGIN_FILE', __FILE__ );
define( 'CDC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CDC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CDC_PLUGIN_DIR . 'includes/class-cdc-taxonomies.php';
require_once CDC_PLUGIN_DIR . 'includes/class-cdc-personalization.php';
require_once CDC_PLUGIN_DIR . 'includes/class-cdc-demo-importer.php';
require_once CDC_PLUGIN_DIR . 'includes/class-cdc-product-data.php';

/**
 * Bootstrap the plugin once all plugins are loaded.
 *
 * Runs on plugins_loaded (after WooCommerce) so modules can rely on
 * WooCommerce being available without racing against its initialization.
 */
function cdc_plugins_loaded() {
	CDC_Taxonomies::register();
	CDC_Personalization::init();
	CDC_Product_Data::init();
}
add_action( 'plugins_loaded', 'cdc_plugins_loaded' );

/**
 * Admin-only modules.
 *
 * Demo content import stays on admin_init so it never blocks the front end.
 */
function cdc_admin_init() {
	CDC_Demo_Importer::init();
}
add_action( 'admin_init', 'cdc_admin_init' );
