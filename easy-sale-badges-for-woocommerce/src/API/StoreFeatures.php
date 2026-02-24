<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\API;

defined( 'ABSPATH' ) || exit;

use AsanaPlugins\WooCommerce\SaleBadges;

class StoreFeatures extends BaseController {

	protected $rest_base = 'store-features';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods' => \WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_store_features_settings' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				[
					'methods' => \WP_REST_Server::CREATABLE,
					'callback' => [ $this, 'save_store_features_settings' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/active',
			[
				[
					'methods' => \WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_active_store_features' ],
					'permission_callback' => '__return_true',
				],
			]
		);
	}

	/**
	 * Get store features settings.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_store_features_settings( $request ) {
		try {
			$feature = ! empty( $request['feature'] ) ? sanitize_text_field( wp_unslash( $request['feature'] ) ) : '';
			if ( empty( $feature ) ) {
				throw new \Exception( __( 'Feature is required.', 'easy-sale-badges-for-woocommerce' ) );
			}

			return rest_ensure_response( [ 'settings' => get_option( "asnp_wesb_store_features_{$feature}_settings", [] ) ] );
		} catch (\Exception $e) {
			return new \WP_Error( 'asnp_wesb_rest_store_features_settings_error', $e->getMessage(), array( 'status' => 400 ) );
		}
	}

	/**
	 * Save settings.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function save_store_features_settings( $request ) {
		try {
			if ( ! $request || empty( $request['data'] ) ) {
				throw new \Exception( __( 'Settings data is required.', 'easy-sale-badges-for-woocommerce' ) );
			}

			$feature = ! empty( $request['feature'] ) ? sanitize_text_field( wp_unslash( $request['feature'] ) ) : '';
			if ( empty( $feature ) ) {
				throw new \Exception( __( 'Feature is required.', 'easy-sale-badges-for-woocommerce' ) );
			}

			$data = [];

			foreach ( $request['data'] as $key => $value ) {
				switch ( $key ) {
					// String values.
					case 'quick_view_text':
					case 'quick_view_type':
					case 'quick_view_position':
					case 'quick_view_on_image':
					case 'quick_view_out_of_image':
					case 'quick_view_icon':
					case 'quick_view_animation_type':
					case 'quick_view_showDesc':
					case 'quick_view_icon_positionX':
					case 'quick_view_icon_positionY':
					case 'qv_theme_popup':
					case 'qv_theme_sidebar':
					case 'quick_view_icon_mob_positionX':
					case 'quick_view_icon_mob_positionY':
					case 'loopCustomHooksQuickView':
					case 'quick_view_on_image_type':
					case 'quick_view_btn_positionY':
					case 'quick_view_btn_mob_positionY':
						$data[ $key ] = sanitize_text_field( wp_unslash( $value ) );
						break;

					// Integer options.
					case 'z_index':
						$data[ $key ] = intval( $value );
						break;

					// Boolean values.
					case 'quick_view':
					case 'quick_view_mobile':
					case 'quick_view_next_before':
					case 'quick_view_show_hover':
					case 'quick_view_button_icon':
						$data[ $key ] = SaleBadges\string_to_bool( $value ) ? 1 : 0;
						break;

					default:
						if ( isset( $value ) ) {
							$data[ sanitize_text_field( wp_unslash( $key ) ) ] = wp_kses_post_deep( $value );
						}
						break;
				}
			}

			if ( empty( $data ) ) {
				throw new \Exception( __( 'Settings data is required.', 'easy-sale-badges-for-woocommerce' ) );
			}

			$data = apply_filters( "asnp_wesb_store_features_{$feature}_settings_save", $data, $feature, $request );
			update_option( "asnp_wesb_store_features_{$feature}_settings", $data );
			do_action( "asnp_wesb_store_features_{$feature}_settings_saved", $data, $feature, $request );

			return rest_ensure_response( [ 'settings' => $data ] );
		} catch (\Exception $e) {
			return new \WP_Error( 'asnp_wesb_rest_store_features_settings_error', $e->getMessage(), array( 'status' => 400 ) );
		}
	}

	/**
	 * Get active store features settings.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_active_store_features( $request ) {
		try {
			$features = [ 'quick_view' ];
			$data = [];

			foreach ( $features as $feature ) {
				$options = get_option( "asnp_wesb_store_features_{$feature}_settings", [] );
				// Ensure the feature is enabled.
				if ( ! empty( $options ) && ! empty( $options[ $feature ] ) ) {
					$data[ $feature ] = $options;
				}
			}

			return rest_ensure_response( [ 'data' => $data ] );
		} catch (\Exception $e) {
			return new \WP_Error( 'asnp_wesb_rest_store_features_settings_error', $e->getMessage(), array( 'status' => 400 ) );
		}
	}

}
