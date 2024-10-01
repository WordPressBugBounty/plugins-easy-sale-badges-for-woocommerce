<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\API;

defined( 'ABSPATH' ) || exit;

use AsanaPlugins\WooCommerce\SaleBadges;

class Ch extends BaseController {

	protected $rest_base = 'ch';

	public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'ch' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
				),
            )
		);
	}

	public function ch( $request ) {
		try {
			if ( empty( $request['action'] ) ) {
				throw new \Exception( __( 'Action is required.', 'easy-sale-badges-for-woocommerce' ) );
			}

			switch ( $request['action'] ) {
				case 'later':
					return $this->later( $request );
					break;

				case 'dismiss':
					return $this->dismiss( $request );
					break;
			}

			throw new \Exception( __( 'Action not found.', 'easy-sale-badges-for-woocommerce' ) );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'asnp_easy_sale_badge_rest_ch_error', $e->getMessage(), array( 'status' => 400 ) );
		}
	}

	protected function later() {
		$ch = SaleBadges\get_ch();
		$ch['schedule'] = strtotime( '+30 days' );
		unset( $ch['show'] );
		SaleBadges\set_ch( $ch );

		return rest_ensure_response( array( 'success' => 1 ) );
	}

	protected function dismiss() {
		$ch = SaleBadges\get_ch();
		$ch['dismissed'] = true;
		unset( $ch['show'] );
		SaleBadges\set_ch( $ch );

		return rest_ensure_response( array( 'success' => 1 ) );
	}

}
