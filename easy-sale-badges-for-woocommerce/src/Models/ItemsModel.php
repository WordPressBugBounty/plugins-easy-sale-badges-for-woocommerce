<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Models;

defined( 'ABSPATH' ) || exit;

use AsanaPlugins\WooCommerce\SaleBadges;

class ItemsModel {

	public static function search_products( array $args = array() ) {
		if ( empty( $args['search'] ) ) {
			throw new \Exception( 'Search term is required to search products.' );
		}

		$data_store = \WC_Data_Store::load( 'product' );

		if ( version_compare( WC_VERSION, '3.5.0', '>=' ) ) {
			$products = $data_store->search_products( wc_clean( wp_unslash( $args['search'] ) ), '', true, true, 50 );
		} else {
			$products = $data_store->search_products( wc_clean( wp_unslash( $args['search'] ) ), '', true, true );
		}

		return ! empty( $products ) ? self::prepare_product_items( $products, ! empty( $args['type'] ) ? $args['type'] : array() ) : array();
	}

	public static function get_products( array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'status'   => array( 'private', 'publish' ),
				'type'     => array( 'simple', 'variation' ),
				'limit'    => -1,
				'orderby'  => array(
					'menu_order' => 'ASC',
					'ID'         => 'DESC',
				),
				'paginate' => false,
			)
		);

		$products = wc_get_products( $args );
		return ! empty( $products ) ? self::prepare_product_items( $products, $args['type'] ) : array();
	}

	protected static function prepare_product_items( array $products, $allowed_types = array( 'simple', 'variation' ) ) {
		if ( empty( $products ) ) {
			return array();
		}

		$products_select = array();
		foreach ( $products as $product ) {
			if ( is_numeric( $product ) ) {
				$product = wc_get_product( $product );
			}
			if ( ! $product ) {
				continue;
			}

			if ( ! SaleBadges\wc_products_array_filter_readable( $product ) ) {
				continue;
			}

			if ( ! empty( $allowed_types ) && ! in_array( $product->get_type(), $allowed_types ) ) {
				continue;
			}

			$id = SaleBadges\maybe_get_exact_item_id( $product->get_id() );
			if ( isset( $products_select[ $id ] ) ) {
				continue;
			}

			if ( $product->get_sku() ) {
				$identifier = $product->get_sku();
			} else {
				$identifier = '#' . $product->get_id();
			}

			if ( 'variation' === $product->get_type() ) {
				$formatted_variation_list = wc_get_formatted_variation( $product, true );
				$text                     = sprintf( '%2$s (%1$s)', $identifier, $product->get_title() ) . ' ' . $formatted_variation_list;
			} else {
				$text = sprintf( '%2$s (%1$s)', $identifier, $product->get_title() );
			}

			$products_select[ $id ] = (object) array(
				'value' => $product->get_id(),
				'label' => $text,
			);
		}

		return array_values( $products_select );
	}

	public static function get_categories( array $args = array() ) {
		$defaults = array(
			'taxonomy'           => 'product_cat',
			'separator'          => '/',
			'nicename'           => false,
			'pad_counts'         => 1,
			'show_count'         => 1,
			'hierarchical'       => 1,
			'hide_empty'         => 0,
			'show_uncategorized' => 0,
			'orderby'            => 'name',
			'menu_order'         => false,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( 'order' === $args['orderby'] ) {
			$args['menu_order'] = 'asc';
			$args['orderby']    = 'name';
		}

		$terms = get_terms( apply_filters( 'asnp_wesb_get_categories_args', $args ) );
		if ( empty( $terms ) ) {
			return array();
		}

		$categories = array();
		foreach ( $terms as $category ) {
			$categories[] = (object) array(
				'value' => $category->term_id,
				'label' => rtrim( SaleBadges\get_term_hierarchy_name( $category->term_id, 'product_cat', $args['separator'], $args['nicename'] ), $args['separator'] ),
				'slug'  => $category->slug,
				'name'  => $category->name,
			);
		}

		return $categories;
	}

	public static function get_tags( array $args = array() ) {
		$args  = wp_parse_args(
			$args,
			array(
				'taxonomy'   => 'product_tag',
				'hide_empty' => 0,
				'nicename'   => false,
			)
		);
		$terms = get_terms( apply_filters( 'asnp_wesb_get_tags_args', $args ) );
		if ( empty( $terms ) ) {
			return array();
		}

		$ret_terms = array();
		foreach ( $terms as $term ) {
			$ret_terms[] = (object) array(
				'value' => $term->term_id,
				'label' => $args['nicename'] ? $term->slug : $term->name,
				'slug'  => $term->slug,
				'name'  => $term->name,
			);
		}

		return $ret_terms;
	}

}
