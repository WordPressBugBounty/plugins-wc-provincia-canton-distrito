<?php
/**
 * Plugin Name: WC Provincia-Canton-Distrito
 * Plugin URI: https://keylormendoza.com/woocommerce/wc-provincia-canton-distrito/
 * Description: This plugin allows you to populate your custom states, cities, and postcodes for WooCommerce. It started working only for Costa Rica but now it is compatible with multi countries.
 * Version: 1.5.0
 * Requires at least: 4.7
 * Tested up to: 6.4.2
 * WC requires at least: 3.0
 * WC tested up to: 8.4.0
 * Author: Keylor Mendoza A.
 * Author URI: https://www.keylormendoza.com
 * License: GPLv2
 * Text Domain: wc-prov-cant-dist
 */

if ( !defined( 'ABSPATH' ) ) { exit; }

if ( !defined( 'WPCD_PLUGIN_VERSION' ) ) {
	define( 'WPCD_PLUGIN_VERSION', '1.5.0' );
}

if ( !defined( 'WPCD_PLUGIN_FILE' ) ) {
	define( 'WPCD_PLUGIN_FILE', plugin_basename( __FILE__ ) );
}

if ( !defined( 'WPCD_PLUGIN_PATH' ) ) {
	define( 'WPCD_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}

if ( !defined( 'WPCD_PLUGIN_DIR_URL' ) ) {
	define( 'WPCD_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Check if WooCommerce is active
 * 
 * @version 1.4.1
 * @since 1.0.0
 */
function wcpcd_inactive_notice() {
	if ( current_user_can( 'activate_plugins') && !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		?>
		<div id="message" class="error">
			<p>
				<?php
				printf(
					/* translators: %1$s Plugin name, %2$s Open a tag, %3$s Close a tag */
					__('%1$s requires %2$sWooCommerce%3$s to be active.', 'wc-prov-cant-dist'),
					'<strong>WC Provincia-Canton-Distrito</strong>',
					'<a href="http://wordpress.org/plugins/woocommerce/" target="_blank" >',
					'</a>'
				);
				?>
			</p>
		</div>		
		<?php
		return;
	}
}
add_action( 'admin_notices', 'wcpcd_inactive_notice' );

include plugin_dir_path( __FILE__ ) . '/includes/wcpcd-class.php';

/**
 * WPCD Init
 */
function wcpcd_init() {
	new WC_PROV_CANT_DIST();
}
add_action( 'init', 'wcpcd_init' );

/**
 * Language
 */
function wcpcd_language_init() {
	load_plugin_textdomain( 'wc-prov-cant-dist', false, dirname( WPCD_PLUGIN_FILE ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wcpcd_language_init' );

/**
 * WC HPOS Compatibility check
 * 
 * @version 1.5.0
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );