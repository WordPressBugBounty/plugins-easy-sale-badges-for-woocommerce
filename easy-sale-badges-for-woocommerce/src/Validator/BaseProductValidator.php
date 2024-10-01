<?php
namespace AsanaPlugins\WooCommerce\SaleBadges\Validator;

defined( 'ABSPATH' ) || exit;

abstract class BaseProductValidator {

	public static function valid_product( $badge, $product ) {
		if ( ! $badge || empty( $badge->items ) ) {
			return false;
		}

		foreach ( $badge->items as $group ) {
			if ( empty( $group ) ) {
				continue;
			}

			$valid = true;
			foreach ( $group as $item ) {
				if ( ! static::is_valid( $item, $product ) ) {
					$valid = false;
					break;
				}
			}
			if ( $valid ) {
				return true;
			}
		}

		return false;
	}

	public static function is_valid( $item, $product ) {
		if ( empty( $item ) || ! $product ) {
			return false;
		}

		if ( ! isset( $item['type'] ) ) {
			return false;
		}

		$is_valid = false;
		if ( is_callable( [ static::class, $item['type'] ] ) ) {
			$is_valid = static::{$item['type']}( $item, $product );
		}

		return apply_filters(
			'asnp_wesb_product_validator_is_valid_' . $item['type'],
			$is_valid,
			$item,
			$product
		);
	}

	public static function all_products( $item, $product ) {
		if ( empty( $item ) || ! $product ) {
			return false;
		}

		return true;
	}

	public static function products( $item, $product ) {
		if ( empty( $item ) || ! $product ) {
			return false;
		}

		if ( empty( $item['items'] ) ) {
			return false;
		}

		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
		if ( ! $product ) {
			return false;
		}

		$items = static::get_items( $item['items'] );
		if ( empty( $items ) ) {
			return false;
		}
		$items = array_map( 'AsanaPlugins\WooCommerce\SaleBadges\maybe_get_exact_item_id', $items );

		$product_id = $product->get_id();
		$parent_id  = $product->is_type( 'variation' ) ? $product->get_parent_id() : 0;

 		if ( isset( $item['selectType'] ) && 'excluded' === $item['selectType'] ) {
			return ! in_array( $product_id, $items ) && ( 0 == $parent_id || ! in_array( $parent_id, $items ) );
		}

		return in_array( $product_id, $items ) || ( 0 < $parent_id && in_array( $parent_id, $items ) );
	}

	public static function categories( $item, $product ) {
		if ( empty( $item ) || ! $product ) {
			return false;
		}

		if ( empty( $item['items'] ) ) {
			return false;
		}

		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
		if ( ! $product ) {
			return false;
		}

		$categories = static::get_items( $item['items'] );
		if ( empty( $categories ) ) {
			return false;
		}
		$categories = array_map( 'AsanaPlugins\WooCommerce\SaleBadges\maybe_get_exact_category_id', $categories );

		$product            = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
		$product_categories = wc_get_product_cat_ids( $product );
		if ( empty( $product_categories ) ) {
			return false;
		}

		if ( isset( $item['selectType'] ) && 'excluded' === $item['selectType'] ) {
			return empty( array_intersect( $product_categories, $categories ) );
		}

		return ! empty( array_intersect( $product_categories, $categories ) );
	}

	public static function tags( $item, $product ) {
		if ( empty( $item ) || ! $product ) {
			return false;
		}

		if ( empty( $item['items'] ) ) {
			return false;
		}

		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
		if ( ! $product ) {
			return false;
		}

		$tags = static::get_items( $item['items'] );
		if ( empty( $tags ) ) {
			return true;
		}
		$tags = array_map( 'AsanaPlugins\WooCommerce\SaleBadges\maybe_get_exact_tag_id', $tags );

		$product      = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
		$product_tags = wc_get_product_term_ids( $product, 'product_tag' );
		if ( empty( $product_tags ) ) {
			return false;
		}

		if ( isset( $item['selectType'] ) && 'excluded' === $item['selectType'] ) {
			return empty( array_intersect( $product_tags, $tags ) );
		}

		return ! empty( array_intersect( $product_tags, $tags ) );
	}

	public static function stock_status( $item, $product ) {
		if ( empty( $item ) || ! $product ) {
			return false;
		}

		if ( empty( $item['items'] ) ) {
			return false;
		}

		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
		if ( ! $product ) {
			return false;
		}

		return $item['items'] === $product->get_stock_status();
	}

	public static function is_on_sale( $item, $product ) {
		if ( empty( $item ) || ! $product ) {
			return false;
		}

		if ( empty( $item['items'] ) ) {
			return false;
		}

		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
		if ( ! $product ) {
			return false;
		}

		if ( 'no' === $item['items'] ) {
			return ! $product->is_on_sale();
		}

		return $product->is_on_sale();
	}

	protected static function get_items( $items ) {
		if ( empty( $items ) ) {
			return [];
		}

		$ids = [];
		foreach ( $items as $item ) {
			if ( is_array( $item ) && ! empty( $item['value'] ) && 0 < absint( $item['value'] ) ) {
				$ids[] = absint( $item['value'] );
			} elseif ( is_object( $item ) && ! empty( $item->value ) && 0 < absint( $item->value ) ) {
				$ids[] = absint( $item->value );
			} elseif ( is_numeric( $item ) && 0 < absint( $item ) ) {
				$ids[] = absint( $item );
			}
		}
		return $ids;
	}

}
