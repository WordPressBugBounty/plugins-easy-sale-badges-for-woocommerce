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
		$this->handle_offers();

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

	protected function add_offer_notice( $offer_name, $start_date, $end_date, $message, $button_label, $button_url, $button_color = '#0071a1' ) {
		if ( SaleBadges\is_pro_active() ) {
			return;
		}

		$name = 'asnp_wesb_' . $offer_name . '_' . date( 'Y' );
		if ( (int) get_option( $name . '_added' ) ) {
			// Is the offer expired.
			if ( time() > strtotime( $end_date . ' 23:59:59' ) ) {
				\WC_Admin_Notices::remove_notice( $name );
				delete_option( $name . '_added' );
			}
			return;
		}

		if ( \WC_Admin_Notices::has_notice( $name ) ) {
			return;
		}

		// Is the offer applicable.
		if (
			time() < strtotime( $start_date . ' 00:00:00' ) ||
			time() > strtotime( $end_date . ' 23:59:59' )
		) {
			return;
		}

		\WC_Admin_Notices::add_custom_notice(
			$name,
			'<p>' . $message . '<a class="button button-primary" style="margin-left: 10px; background: ' . esc_attr( $button_color ) . '; border-color: ' . esc_attr( $button_color ) . ';" target="_blank" href="' . esc_url( $button_url ) . '">' . esc_html( $button_label ) . '</a></p>'
		);

		update_option( $name . '_added', 1 );
	}

	protected function handle_offers() {
		$this->add_offer_notice(
			'black_friday',
			date( 'Y' ) . '-11-20',
			date( 'Y' ) . '-11-30',
			'<strong>Black Friday Exclusive:</strong> SAVE up to 75% & get access to <strong>WooCommerce Sale Badges and Product Labels</strong> features.',
			'Grab The Offer',
			'https://asanaplugins.com/product/woocommerce-sale-badges-and-product-labels/?utm_source=woocommerce-sale-badges-and-product-labels&utm_campaign=black-friday&utm_medium=link',
			'#5614d5'
		);
		$this->add_offer_notice(
			'cyber_monday',
			date( 'Y' ) . '-12-01',
			date( 'Y' ) . '-12-10',
			'<strong>Cyber Monday Exclusive:</strong> Save up to 75% on <strong>WooCommerce Sale Badges and Product Labels</strong>. Limited Time Only!',
			'Claim Your Deal',
			'https://asanaplugins.com/product/woocommerce-sale-badges-and-product-labels/?utm_source=woocommerce-sale-badges-and-product-labels&utm_campaign=cyber-monday&utm_medium=link',
			'#00aaff'
		);
		$this->add_offer_notice(
			'holiday_discount',
			date( 'Y' ) . '-12-11',
			date( 'Y' ) . '-12-31',
			'<strong>Holiday Cheer:</strong> Get up to 75% OFF <strong>WooCommerce Sale Badges and Product Labels</strong> this festive season.',
			'Shop Now',
			'https://asanaplugins.com/product/woocommerce-sale-badges-and-product-labels/?utm_source=woocommerce-sale-badges-and-product-labels&utm_campaign=holiday-discount&utm_medium=link',
			'#28a745'
		);
		$this->add_offer_notice(
			'new_year_sale',
			date( 'Y' ) . '-01-01',
			date( 'Y' ) . '-01-10',
			'<strong>New Year Sale:</strong> Kickstart your projects with up to 75% OFF <strong>WooCommerce Sale Badges and Product Labels</strong>!',
			'Get The Deal',
			'https://asanaplugins.com/product/woocommerce-sale-badges-and-product-labels/?utm_source=woocommerce-sale-badges-and-product-labels&utm_campaign=new-year-sale&utm_medium=link',
			'#ff5733'
		);
		$this->add_offer_notice(
			'spring_sale',
			date( 'Y' ) . '-03-20',
			date( 'Y' ) . '-03-30',
			'<strong>Spring Into Savings:</strong> Get up to 75% OFF <strong>WooCommerce Sale Badges and Product Labels</strong>. Refresh your store this season!',
			'Spring Deals',
			'https://asanaplugins.com/product/woocommerce-sale-badges-and-product-labels/?utm_source=woocommerce-sale-badges-and-product-labels&utm_campaign=spring-sale&utm_medium=link',
			'#5cb85c'
		);
		$this->add_offer_notice(
			'summer_sale',
			date( 'Y' ) . '-06-15',
			date( 'Y' ) . '-06-25',
			'<strong>Sizzling Summer Sale:</strong> Save up to 75% on <strong>WooCommerce Sale Badges and Product Labels</strong>. Limited time only!',
			'Cool Deals',
			'https://asanaplugins.com/product/woocommerce-sale-badges-and-product-labels/?utm_source=woocommerce-sale-badges-and-product-labels&utm_campaign=summer-sale&utm_medium=link',
			'#ff5733'
		);
		$this->add_offer_notice(
			'halloween_sale',
			date( 'Y' ) . '-10-25',
			date( 'Y' ) . '-10-31',
			'<strong>Halloween Spooktacular:</strong> Scare away high prices! Get up to 75% OFF <strong>WooCommerce Sale Badges and Product Labels</strong>. No tricks, just treats!',
			'Shop Spooky Deals',
			'https://asanaplugins.com/product/woocommerce-sale-badges-and-product-labels/?utm_source=woocommerce-sale-badges-and-product-labels&utm_campaign=halloween-sale&utm_medium=link',
			'#ff4500'
		);
	}

}
