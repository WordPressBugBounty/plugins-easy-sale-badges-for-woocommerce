<?php

namespace AsanaPlugins\WooCommerce\SaleBadges;

defined( 'ABSPATH' ) || exit;

use AsanaPlugins\WooCommerce\SaleBadges\Helpers\Cart;
use function AsanaPlugins\WooCommerce\SaleBadges\Helpers\QuickView\get_product_data;

class Ajax {

	public static function init() {
		add_action( 'wp_ajax_asnp_wesb_quick_view', [ __CLASS__, 'quick_view' ] );
		add_action( 'wp_ajax_nopriv_asnp_wesb_quick_view', [ __CLASS__, 'quick_view' ] );
		add_action( 'wp_ajax_asnp_wesb_add_to_cart_qv', [ __CLASS__, 'add_product_to_cart' ] );
		add_action( 'wp_ajax_nopriv_asnp_wesb_add_to_cart_qv', [ __CLASS__, 'add_product_to_cart' ] );
	}

	public static function quick_view() {
		$product_id = isset( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : 0;

		if ( ! $product_id ) {
			wp_send_json_error(
				[ 'message' => __( 'Product ID is required.', 'easy-sale-badges-for-woocommerce' ) ],
				400
			);
		}

		$data = get_product_data( $product_id );

		if ( empty( $data ) ) {
			wp_send_json_error(
				[ 'message' => __( 'Product not found.', 'easy-sale-badges-for-woocommerce' ) ],
				404
			);
		}

		global $post;
		$post = get_post( $product_id );
		setup_postdata( $post );

		$prev_post = get_adjacent_post( false, '', true );
		$next_post = get_adjacent_post( false, '', false );

		$data['prev_id'] = $prev_post ? $prev_post->ID : null;
		$data['next_id'] = $next_post ? $next_post->ID : null;

		wp_reset_postdata();

		wp_send_json_success( [ 'product' => $data ] );
	}

	public static function add_product_to_cart() {
		check_ajax_referer( 'asnp_wesb_store_features', 'nonce' );
		Cart\ajax_add_to_cart();
	}

}