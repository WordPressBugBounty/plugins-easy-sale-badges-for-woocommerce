<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Helpers\Cart;

defined( 'ABSPATH' ) || exit;

function ajax_add_to_cart() {
	wc_clear_notices();

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$quantity = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );

	$variations = [];

	if ( ! $product_id ) {
		wp_send_json_error( [ 'message' => __( 'Invalid Product ID.', 'easy-sale-badges-for-woocommerce' ) ] );
	}

	foreach ( $_POST as $key => $value ) {
		if ( strpos( $key, 'attribute_' ) === 0 ) {
			$variations[ sanitize_title( $key ) ] = wp_unslash( $value );
		}
	}

	$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

	if ( $passed_validation ) {
		$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );

		if ( $cart_item_key ) {
			\WC_AJAX::get_refreshed_fragments();
		} else {
			wp_send_json( [
				'error' => true,
				'message' => __( 'Could not add product to cart.', 'easy-sale-badges-for-woocommerce' )
			] );
		}
	}

	wp_send_json( [
		'error' => true,
		'message' => __( 'Product validation failed.', 'easy-sale-badges-for-woocommerce' )
	] );
}
