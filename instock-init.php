<?php
/**
 * Plugin Name: TMSM WooCommerce In-Stock Notifier
 * Version: 1.1.7
 * Plugin URI: https://github.com/thermesmarins/tmsm-woocommerce-stocknotifier
 * Author: Thermes Marins de Saint-Malo
 * Author URI: https://github.com/thermesmarins/
 * Description: Customers can build a waiting list of products those are out of stock. They will be notified automatically via email, when products come back in stock.
 * Text Domain:tmsm-woocommerce-stocknotifier
 * Domain Path: /languages/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * WC requires at least: 5.0
 * WC tested up to: 8.3
 * Original Author: Govind Kumar <gkprmr@gmail.com>
 * Github Plugin URI: https://github.com/thermesmarins/tmsm-woocommerce-stocknotifier
 * Github Branch:     master
 * Requires PHP:      7.4
 **/

use InStockNotifier\WSN_Bootstrap;

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( ! function_exists( 'wp_get_current_user' ) ) {
	include( ABSPATH . 'wp-includes/pluggable.php' );
}

// defines
define( 'WSN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WSN_INCLUDE_PATH', WSN_PATH . 'inc' . DIRECTORY_SEPARATOR );
define( 'WSN_ASSEST_PATH', plugin_dir_url( __FILE__ ) . 'assets' . DIRECTORY_SEPARATOR );
define( 'WSN_CLASS_PATH', WSN_PATH . 'inc' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR );
define( 'WSN_EMAIL_TEMPLATE_PATH', WSN_PATH . 'templates' . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR );

define( 'WSN_USERS_META_KEY', 'wsn_waitlist_users' );
define( 'WSN_NUM_META', 'wsn_total_num_waitlist' );

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

// Deactivate the plugin of woocommerce isn't activated.
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	add_action( 'plugins_loaded', 'wsn_pre_load' );
	load_plugin_textdomain( 'tmsm-woocommerce-stocknotifier', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
} else {
	deactivate_plugins( plugin_basename( __FILE__ ) );
	add_action( 'admin_notices', 'wsc_woo_requires' );
}

/**
 * Translate plugins
 *
 * @since 1.0.4
 */
add_action( 'init', 'wsn_localization_plugin' );

/**
 * Load plugin's language file
 */
function wsn_localization_plugin() {
    load_plugin_textdomain( 'tmsm-woocommerce-stocknotifier', false, dirname(plugin_basename(__FILE__)) . '/languages/' );
}

/**
 * If WooCommerce isn't activated then show the admin the notice to activate
 * the WooCommerce plugin order to make this plugin runnable.
 *
 * @since 1.0
 */
function wsc_woo_requires() {
	?>
    <div class="error">
        <p>
			<?php echo esc_html__( 'In-Stock Notifier can\'t active because it requires WooCommerce in order to work.', 'tmsm-woocommerce-stocknotifier' ); ?>
        </p>
    </div>
	<?php
}

/**
 * Make the plugin's main class globally accessible.
 *
 * @since 1.0
 */
function wsn_pre_load() {

	// Loader files.
	require( 'load.php' );

	// Include the all of the functions.
	include_once( WSN_INCLUDE_PATH . 'wsn-func.php' );

	// Making the wsn class global.
	$GLOBALS['instock_alert'] = new WSN_Bootstrap();

}
