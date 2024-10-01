<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\API;

defined( 'ABSPATH' ) || exit;

use AsanaPlugins\WooCommerce\SaleBadges\Models\ItemsModel;

class Items extends BaseController {

	protected $rest_base = 'items';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'search_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Search items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function search_items( $request ) {
		try {
			if ( empty( $request['search'] ) ) {
				throw new \Exception( __( 'Search term is required.', 'easy-sale-badges-for-woocommerce' ) );
			}

			if ( empty( $request['type'] ) ) {
				throw new \Exception( __( 'Type is required.', 'easy-sale-badges-for-woocommerce' ) );
			}

			$search = sanitize_text_field( wp_unslash( $request['search'] ) );
			if ( empty( $search ) ) {
				throw new \Exception( __( 'Search term is required.', 'easy-sale-badges-for-woocommerce' ) );
			}

			$items = [];
			if ( 'products' === $request['type'] ) {
				$items = ItemsModel::search_products(
					array(
						'search' => $search,
						'type'   => array_merge( array_keys( wc_get_product_types() ), ['variation'] ),
					)
				);
			} elseif ( 'categories' === $request['type'] ) {
				$items = ItemsModel::get_categories( array( 'name__like' => $search ) );
			} elseif ( 'tags' === $request['type'] ) {
				$items = ItemsModel::get_tags( array( 'name__like' => $search ) );
			} else {
				$items = apply_filters( 'asnp_wesb_items_api_' . __FUNCTION__, $items, $search, $request );
			}

			return rest_ensure_response( [
				'items' => $items,
			] );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'asnp_wesb_rest_search_items_error', $e->getMessage(), array( 'status' => 400 ) );
		}
	}

	/**
	 * Get items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		try {
			if ( empty( $request['items'] ) ) {
				throw new \Exception( __( 'Items are required.', 'easy-sale-badges-for-woocommerce' ) );
			}

			if ( empty( $request['type'] ) ) {
				throw new \Exception( __( 'Type is required.', 'easy-sale-badges-for-woocommerce' ) );
			}

			$items = $request['items'];
			if ( ! is_array( $items ) ) {
				$items = explode( ',', $items );
			}

			$items = array_filter( array_map( 'absint', $items ) );
			if ( empty( $items ) ) {
				throw new \Exception( __( 'Invalid items.', 'easy-sale-badges-for-woocommerce' ) );
			}

			if ( 'products' === $request['type'] ) {
				$items = ItemsModel::get_products(
				  array(
					'type'    => array_merge( array_keys( wc_get_product_types() ), ['variation'] ),
					'include' => array_map( 'AsanaPlugins\WooCommerce\SaleBadges\maybe_get_exact_item_id', $items ),
				  )
				);
			} elseif ( 'categories' === $request['type'] ) {
				$items = ItemsModel::get_categories( array( 'include' => array_map( 'AsanaPlugins\WooCommerce\SaleBadges\maybe_get_exact_category_id', $items ) ) );
			}  elseif ( 'tags' === $request['type'] ) {
				$items = ItemsModel::get_tags( array( 'include' => array_map( 'AsanaPlugins\WooCommerce\SaleBadges\maybe_get_exact_tag_id', $items ) ) );
			} else {
				$items = apply_filters( 'asnp_wesb_items_api_' . __FUNCTION__, [], $items, $request );
			}

			return rest_ensure_response( [
				'items' => $items,
			] );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'asnp_wesb_rest_get_items_error', $e->getMessage(), array( 'status' => 400 ) );
		}
	}

}
