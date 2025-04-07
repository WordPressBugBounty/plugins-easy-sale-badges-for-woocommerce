<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Admin;

defined( 'ABSPATH' ) || exit;

use AsanaPlugins\WooCommerce\SaleBadges;

class Assets {

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ), 15 );
	}

	public function load_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( 'toplevel_page_asnp-easy-sale-badge' === $screen_id ) {
			SaleBadges\register_polyfills();

			wp_enqueue_style(
				'asnp-easy-sale-badge-admin',
				apply_filters( 'asnp_wesb_sale_badge_admin_style', $this->get_url( 'admin/style', 'css' ) )
			);
			wp_enqueue_script(
				'asnp-easy-sale-badge-admin',
				apply_filters( 'asnp_wesb_sale_badge_admin_script', $this->get_url( 'admin/admin/index', 'js' ) ),
				array(
					'moment',
					'react-dom',
					'wp-hooks',
					'wp-i18n',
					'wp-api-fetch',
				),
				ASNP_WESB_VERSION,
				true
			);

			wp_localize_script(
				'asnp-easy-sale-badge-admin',
				'saleBadgeData',
				apply_filters( 'asnp_wesb_sale_badge_admin_localize_script', array(
					'pluginUrl'  => ASNP_WESB_PLUGIN_URL,
					'timezone'   => SaleBadges\get_timezone_string(),
					'now'        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
					'stylesheet' => get_stylesheet(),
					'template'   => get_template(),
				) )
			);

			if ( function_exists( 'wp_set_script_translations' ) ) {
				$domain    = SaleBadges\is_pro_active() ? 'easy-sale-badges-for-woocommerce-pro' : 'easy-sale-badges-for-woocommerce';
				$lang_path = defined( 'ASNP_WESB_PRO_ABSPATH' ) ? ASNP_WESB_PRO_ABSPATH : ASNP_WESB_ABSPATH;
				wp_set_script_translations(
					'asnp-easy-sale-badge-admin',
					$domain,
					apply_filters( 'asnp_wesb_sale_badge_admin_script_translations', $lang_path . 'languages' )
				);
			}
		}
	}

	public function get_url( $file, $ext ) {
		return plugins_url( $this->get_path( $ext ) . $file . '.' . $ext, ASNP_WESB_PLUGIN_FILE );
	}

	protected function get_path( $ext ) {
		return 'css' === $ext ? 'assets/css/' : 'assets/js/';
	}

}
