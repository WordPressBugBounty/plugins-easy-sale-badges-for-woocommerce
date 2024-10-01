<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Admin;

defined( 'ABSPATH' ) || exit;

use AsanaPlugins\WooCommerce\SaleBadges\Registry\Container;
use AsanaPlugins\WooCommerce\SaleBadges;

class Admin {

	protected $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function init() {
		$this->register_dependencies();
		$this->black_friday();

		$this->container->get( Assets::class )->init();
		$this->container->get( Menu::class )->init();

		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
	}

	protected function register_dependencies() {
		$this->container->register(
			Menu::class,
			function ( Container $container ) {
				return new Menu();
			}
		);
		$this->container->register(
			Assets::class,
			function ( Container $container ) {
				return new Assets();
			}
		);
	}

	/**
	 * Plugin row meta links
	 * This function adds additional links below the plugin in admin plugins page.
	 *
	 * @since  1.0.0
	 * @param  array  $links    The array having default links for the plugin.
	 * @param  string $file     The name of the plugin file.
	 * @return array  $links    Plugin default links and specific links.
	 */
	public function plugin_row_meta_links( $links, $file ) {
		if ( false === strpos( $file, 'easy-sale-badges.php' ) ) {
			return $links;
		}

		if ( SaleBadges\is_pro_active() ) {
			return $links;
		}

		$links = array_merge(
			$links,
			[ '<a href="https://www.asanaplugins.com/product/woocommerce-sale-badges-and-product-labels/" target="_blank" onMouseOver="this.style.color=\'#55ce5a\'" onMouseOut="this.style.color=\'#39b54a\'" style="color: #39b54a; font-weight: bold;">' . esc_html__( 'Go Pro', 'easy-sale-badges-for-woocommerce' ) . '</a>' ]
		);

		return $links;
	}

	/**
	 * Plugin action links
	 * This function adds additional links below the plugin in admin plugins page.
	 *
	 * @since  1.0.0
	 *
	 * @param  array  $links    The array having default links for the plugin.
	 * @param  string $file     The name of the plugin file.
	 *
	 * @return array  $links    Plugin default links and specific links.
	 */
	public function plugin_action_links( $links, $file ) {
		if ( false === strpos( $file, 'easy-sale-badges.php' ) ) {
			return $links;
		}

		$extra = [ '<a href="' . admin_url( 'admin.php?page=asnp-easy-sale-badge#/settings' ) . '">' . esc_html__( 'Settings', 'easy-sale-badges-for-woocommerce' ) . '</a>' ];

		if ( ! SaleBadges\is_pro_active() ) {
			$extra[] = '<a href="https://www.asanaplugins.com/product/woocommerce-sale-badges-and-product-labels/?utm_source=sale-badges-for-woocommerce&utm_campaign=go-pro&utm_medium=link" target="_blank" onMouseOver="this.style.color=\'#55ce5a\'" onMouseOut="this.style.color=\'#39b54a\'" style="color: #39b54a; font-weight: bold;">' . esc_html__( 'Go Pro', 'easy-sale-badges-for-woocommerce' ) . '</a>';
		}

		return array_merge( $links, $extra );
	}

	protected function black_friday() {
		if ( SaleBadges\is_pro_active() ) {
			return;
		}

		$name = 'asnp_wesb_black_friday_' . date( 'Y' );
		if ( (int) get_option( $name . '_added' ) ) {
			// Is Black Friday expired.
			if ( time() > strtotime( date( 'Y' ) . '-11-30' ) ) {
				\WC_Admin_Notices::remove_notice( $name );
				delete_option( $name . '_added' );
			}
			return;
		}

		if ( \WC_Admin_Notices::has_notice( $name ) ) {
			return;
		}

		// Is Black Friday applicable.
		if (
			time() < strtotime( date( 'Y' ) . '-11-00' ) ||
			time() > strtotime( date( 'Y' ) . '-11-30' )
		) {
			return;
		}

		\WC_Admin_Notices::add_custom_notice(
			$name,
			'<p>' . __( '<strong>Black Friday Exclusive:</strong> SAVE up to 50% & access to <strong>Sale Badges for WooCommerce PRO</strong> features.', 'easy-sale-badges-for-woocommerce' ) . '<a class="button button-primary" style="margin-left: 10px; background: #5614d5; border-color: #5614d5;" target="_blank" href="https://asanaplugins.com/product/woocommerce-sale-badges-and-product-labels/?utm_source=sale-badges-for-woocommerce&utm_campaign=black-friday&utm_medium=link">' . __( 'Grab The Offer', 'easy-sale-badges-for-woocommerce' ) . '</a></p>'
		);

		update_option( $name . '_added', 1 );
	}

}
