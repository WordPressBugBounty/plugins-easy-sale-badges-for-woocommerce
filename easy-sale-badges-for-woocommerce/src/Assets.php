<?php

namespace AsanaPlugins\WooCommerce\SaleBadges;

defined( 'ABSPATH' ) || exit;

class Assets {

	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 15 );
		add_action( 'wp_footer', array( $this, 'localize_scripts' ), 15 );
	}

	public function load_scripts() {
		if ( has_active_sale_badges() ) {
			wp_enqueue_style(
				'asnp-wesb-badge',
				apply_filters( 'asnp_wesb_badge_style', $this->get_url( 'badge/style', 'css' ) )
			);
			wp_enqueue_script(
				'asnp-wesb-badge',
				apply_filters( 'asnp_wesb_badge_script', $this->get_url( 'badge/index', 'js' ) ),
				[ 'jquery' ],
				ASNP_WESB_VERSION,
				true
			);
		}
	}

	public function localize_scripts() {
		$container = get_theme_single_container();
		if ( empty( $container ) ) {
			$container = get_plugin()->settings->get_setting( 'singleContainer', '' );
		}

		$stylesheet = empty( $stylesheet ) ? get_stylesheet() : $stylesheet;
		$template   = empty( $template ) ? get_template() : $template;

		$stylesheet = ! empty( $stylesheet ) ? strtolower( $stylesheet ) : $stylesheet;
		$template   = ! empty( $template ) ? strtolower( $template ) : $template;

		wp_localize_script(
			'asnp-wesb-badge',
			'asnpWesbBadgeData',
			[
				'singleContainer' => $container,
				'stylesheet' => $stylesheet,
				'template' => $template,
			]
		);
	}

	public function get_url( $file, $ext ) {
		return plugins_url( $this->get_path( $ext ) . $file . '.' . $ext, ASNP_WESB_PLUGIN_FILE );
	}

	protected function get_path( $ext ) {
		return 'css' === $ext ? 'assets/css/' : 'assets/js/';
	}

}
