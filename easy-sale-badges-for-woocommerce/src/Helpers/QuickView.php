<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Helpers\QuickView;

defined( 'ABSPATH' ) || exit;

use AsanaPlugins\WooCommerce\SaleBadges;

function get_product_data( $product_id ) {
	$product = wc_get_product( $product_id );

	if ( ! $product ) {
		return [];
	}

	$data = [
		'id' => $product->get_id(),
		'name' => $product->get_name(),
		'product_class' => implode( ' ', wc_get_product_class( '', $product ) ),
		'type' => $product->get_type(),
		'price_html' => $product->get_price_html(),
		'short_description' => wpautop( $product->get_short_description() ),
		'description' => wpautop( $product->get_description() ),
		'images' => [
			'main' => wp_get_attachment_image_url( $product->get_image_id(), 'large' ),
			'gallery' => array_values(
				array_map(
					function ( $id ) {
						return wp_get_attachment_image_url( $id, 'large' );
					},
					$product->get_gallery_image_ids()
				)
			),
		],
		'permalink' => get_permalink( $product_id ),
	];

	$data['add_to_cart_html'] = get_add_to_cart_html( $product_id );

	if ( $product->is_type( 'variable' ) || $product->is_type( 'variation' ) ) {
		$data['parent_id'] = $product->get_parent_id();
	}

	return $data;
}

function get_add_to_cart_html( $product_id ) {
	if ( ! $product_id ) {
		return '';
	}

	global $product;
	$original_global_product = $product;

	ob_start();

	$product = wc_get_product( $product_id );
	if ( $product ) {
		woocommerce_template_single_add_to_cart();
	}

	$html = ob_get_clean();

	$product = $original_global_product;

	return $html;
}
