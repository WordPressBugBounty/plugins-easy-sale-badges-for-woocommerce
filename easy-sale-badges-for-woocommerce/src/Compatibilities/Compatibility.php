<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Compatibilities;

defined( 'ABSPATH' ) || exit;

class Compatibility {

	public static function init() {
		// WPML compatibility.
		if ( function_exists( 'wpml_loaded' ) ) {
			WPML::init();
		}
	}

}
