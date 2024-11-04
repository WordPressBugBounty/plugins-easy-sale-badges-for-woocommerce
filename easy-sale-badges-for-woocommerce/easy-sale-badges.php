<?php
/**
 * @wordpress-plugin
 * Plugin Name: Sale Badges and Product Labels for WooCommerce
 * Plugin URI: https://www.asanaplugins.com/product/woocommerce-product-labels/
 * Description: Sale Badges and Product Labels for WooCommerce
 * Tags: sale badge, product label, woocommerce, badge, woocommerce badge, woocommerce sale badge, labels, plugin, shop, store, ecommerce, marketing, products, tags, product tags, product marks, product sign, sale product label, onsale product, woocommerce tags, custom labels, custom product badges, advanced product labels for woocommerce
 * Version: 3.7.0
 * Author: Asana Plugins
 * Author URI: http://www.asanaplugins.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: easy-sale-badges-for-woocommerce
 * Domain Path: /languages
 * WC requires at least: 3.0
 * WC tested up to: 9.3.3
 * Requires Plugins: woocommerce 
 *
 * Copyright 2023 Asana Plugins (http://www.asanaplugins.com/)
 */

defined( 'ABSPATH' ) || exit;

use AsanaPlugins\WooCommerce\SaleBadges\Plugin;

// Plugin version.
if ( ! defined( 'ASNP_WESB_VERSION' ) ) {
	define( 'ASNP_WESB_VERSION', '3.7.0' );
}

/**
 * Autoload packages.
 *
 * We want to fail gracefully if `composer install` has not been executed yet, so we are checking for the autoloader.
 * If the autoloader is not present, let's log the failure and display a nice admin notice.
 */
$autoloader = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $autoloader ) ) {
	require $autoloader;
} else {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log(  // phpcs:ignore
			sprintf(
				/* translators: 1: composer command. 2: plugin directory */
				esc_html__( 'Your installation of the Easy Sale Badges For Woocommerce plugin is incomplete. Please run %1$s within the %2$s directory.', 'easy-sale-badges-for-woocommerce' ),
				'`composer install`',
				'`' . esc_html( str_replace( ABSPATH, '', __DIR__ ) ) . '`'
			)
		);
	}
	/**
	 * Outputs an admin notice if composer install has not been ran.
	 */
	add_action(
		'admin_notices',
		function() {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					printf(
						/* translators: 1: composer command. 2: plugin directory */
						esc_html__( 'Your installation of Easy Sale Badges For Woocommerce plugin is incomplete. Please run %1$s within the %2$s directory.', 'easy-sale-badges-for-woocommerce' ),
						'<code>composer install</code>',
						'<code>' . esc_html( str_replace( ABSPATH, '', __DIR__ ) ) . '</code>'
					);
					?>
				</p>
			</div>
			<?php
		}
	);
	return;
}

/**
 * The main function for that returns Plugin
 *
 * The main function responsible for returning the one true Plugin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = ASNP_WESB(); ?>
 *
 * @since  1.0.0
 * @return object|Plugin The one true Plugin Instance.
 */
function ASNP_WESB() {
	return Plugin::instance();
}
ASNP_WESB()->init();
