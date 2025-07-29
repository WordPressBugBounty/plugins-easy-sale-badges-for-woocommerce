<?php

namespace AsanaPlugins\WooCommerce\SaleBadges;

use AsanaPlugins\WooCommerce\SaleBadges\Models\BadgeModel;
use AsanaPlugins\WooCommerce\SaleBadges\Validator\ProductValidator;
use AsanaPlugins\WooCommerce\SaleBadges\Validator\DateTimeValidator;
use function AsanaPlugins\WooCommerce\SaleBadges\Helpers\Badges\output_badge;

function get_plugin() {
	return Plugin::instance();
}

/**
 * Callback for array filter to get products the user can view only.
 *
 * @since  1.0.0
 *
 * @param  \WC_Product $product WC_Product object.
 *
 * @return bool
 */
function wc_products_array_filter_readable( $product ) {
	if ( function_exists( '\wc_products_array_filter_readable' ) ) {
		return \wc_products_array_filter_readable( $product );
	}

	return $product && is_a( $product, 'WC_Product' ) && current_user_can( 'read_product', $product->get_id() );
}

/**
 * Converts a string (e.g. 'yes' or 'no') to a bool.
 *
 * @since 1.0.0
 *
 * @param string|bool $string String to convert. If a bool is passed it will be returned as-is.
 *
 * @return bool
 */
function string_to_bool( $string ) {
	return is_bool( $string ) ? $string : ( 'yes' === strtolower( $string ) || 1 === $string || 'true' === strtolower( $string ) || '1' === $string );
}

/**
 * Retrieves the timezone of the site as a string.
 *
 * @since 1.0.0
 *
 * @return string timezone name.
 */
function get_timezone_string() {
	$tzstring = get_option( 'timezone_string' );

	// Remove old Etc mappings. Fallback to gmt_offset.
	if ( false !== strpos( $tzstring, 'Etc/GMT' ) ) {
		$tzstring = '';
	} elseif ( false !== strpos( $tzstring, 'UTC' ) ) {
		$tzstring = 'UTC';
	}

	return ! empty( $tzstring ) ? $tzstring : 'UTC';
}

function get_term_hierarchy_name( $term_id, $taxonomy, $separator = '/', $nicename = false, $visited = array() ) {
	$chain = '';
	$term = get_term( $term_id, $taxonomy );

	if ( is_wp_error( $term ) ) {
		return '';
	}

	$name = $term->name;
	if ( $nicename ) {
		$name = $term->slug;
	}

	if ( $term->parent && ( $term->parent != $term->term_id ) && ! in_array( $term->parent, $visited ) ) {
		$visited[] = $term->parent;
		$chain     .= get_term_hierarchy_name( $term->parent, $taxonomy, $separator, $nicename, $visited );
	}

	$chain .= $name . $separator;

	return $chain;
}

function register_polyfills() {
	static $registered;
	if ( $registered ) {
		return;
	}

	global $wp_version;

	$handles = array(
		'react'        => array( '17.0.2', array() ),
		'react-dom'    => array( '17.0.2', array( 'react' ) ),
		'wp-i18n'      => array( '6.0', array() ),
		'wp-hooks'     => array( '6.0', array() ),
		'wp-api-fetch' => array( '6.0', array() ),
		'moment'       => array( '2.29.4', array() ),
	);
	foreach ( $handles as $handle => $value ) {
		if ( ! version_compare( $wp_version, '5.9', '>=' ) && in_array( $handle, array( 'react', 'react-dom' ) ) ) {
			wp_deregister_script( $handle );
		}

		if ( ! wp_script_is( $handle, 'registered' ) ) {
			wp_register_script(
				$handle,
				plugins_url( 'assets/js/vendor/' . $handle . '.js', ASNP_WESB_PLUGIN_FILE ),
				$value[1],
				$value[0],
				true
			);
		}
	}

	$registered = true;
}

function is_pro_active() {
	return defined( 'ASNP_WESB_PRO_VERSION' );
}

function add_custom_style( $style, $badge = null ) {
	if ( empty( $style ) ) {
		return;
	}

	get_plugin()->container()->get( CustomStyles::class )->add_style( $style, $badge );
}

function display_sale_badges( $product, $hide = false, $return = false, $out_of_image = false ) {
	if ( ! $product ) {
		return '';
	}

	$badges = get_plugin()->container()->get( Badges::class );
	if ( ! $badges ) {
		return '';
	}

	return $badges->display_badges( $product, $hide, $return, $out_of_image );
}

function display_sale_badge( $id, $product, $hide = false, $return = false, $out_of_image = false ) {
	if ( ! $id || 0 >= $id ) {
		return '';
	}

	if ( ! $product ) {
		return '';
	}

	$model = get_plugin()->container()->get( BadgeModel::class );
	$badge = $model->get_item( $id );
	if ( ! $badge ) {
		return '';
	}

	if ( ! isset( $badge->status ) || 1 != $badge->status ) {
		return '';
	}

	if ( ! ProductValidator::valid_product( $badge, $product ) ) {
		return '';
	}

	// Validate schedule if exists.
	if (
		! empty( $badge->schedule ) &&
		! DateTimeValidator::is_valid_date_times( $badge->schedule )
	) {
		return '';
	}

	return output_badge( $product, $badge, $hide, $return, $out_of_image );
}

function has_active_sale_badges() {
	$model = get_plugin()->container()->get( BadgeModel::class );
	$items = $model->get_items( [ 'status' => 1 ] );
	return ! empty( $items );
}

function get_current_product() {
	global $product;

	if ( $product && is_a( $product, 'WC_Product' ) ) {
		return $product;
	}

	global $post;
	if ( ! $post ) {
		return false;
	}

	if ( is_a( $post, 'WC_Product' ) ) {
		return $post;
	}

	$woo_product = wc_get_product( $post->ID );
	if ( $woo_product && is_a( $woo_product, 'WC_Product' ) ) {
		return $woo_product;
	}

	return false;
}

function is_product_page() {
	if ( is_product() ) {
		return true;
	}

	global $post;
	if ( empty( $post ) || empty( $post->post_content ) ) {
		return false;
	}

	if (
		false !== strpos( $post->post_content, '[product_page' ) ||
		false !== strpos( $post->post_content, '[asnp_wepb_product' )
	) {
		return true;
	}

	return false;
}

function get_theme_loop_position( $stylesheet = null, $template = null ) {
	$stylesheet = empty( $stylesheet ) ? get_stylesheet() : $stylesheet;
	$template   = empty( $template ) ? get_template() : $template;

	$stylesheet = ! empty( $stylesheet ) ? strtolower( $stylesheet ) : $stylesheet;
	$template   = ! empty( $template ) ? strtolower( $template ) : $template;

	$themes = [
		'avada'    	  => 'after_shop_loop_item_thumbnail',
		'woodmart' 	  => 'after_shop_loop_item_thumbnail',
		'porto'    	  => 'after_shop_loop_item_thumbnail',
		'oceanwp'     => 'ocean_before_archive_product_image',
		'basel'   	  => 'after_shop_loop_item_thumbnail',
		'thegem'      => 'after_shop_loop_item_thumbnail',
		'uncode'      => 'uncode_entry_visual_after_image',
		'rehub-theme' => 'rh_woo_thumbnail_loop',
		'total'       => 'wpex_woocommerce_loop_thumbnail_before',
		'enfold'      => 'post_thumbnail_html',
		'estore'      => 'after_shop_loop_item_thumbnail',
	];

	if ( ! empty( $stylesheet ) && isset( $themes[ $stylesheet ] ) ) {
		return $themes[ $stylesheet ];
	}

	if ( ! empty( $template ) && isset( $themes[ $template ] ) ) {
		return $themes[ $template ];
	}

	return '';
}

function get_theme_out_of_image_loop_position( $stylesheet = null, $template = null ) {
	$stylesheet = empty( $stylesheet ) ? get_stylesheet() : $stylesheet;
	$template   = empty( $template ) ? get_template() : $template;

	$stylesheet = ! empty( $stylesheet ) ? strtolower( $stylesheet ) : $stylesheet;
	$template   = ! empty( $template ) ? strtolower( $template ) : $template;

	$themes = [
		'betheme' 	  => 'woocommerce_after_shop_loop_item_title',
		'uncode'      => 'uncode_entry_visual_after_image',
		'oceanwp'      => 'ocean_before_archive_product_categories',
	];

	if ( ! empty( $stylesheet ) && isset( $themes[ $stylesheet ] ) ) {
		return $themes[ $stylesheet ];
	}

	if ( ! empty( $template ) && isset( $themes[ $template ] ) ) {
		return $themes[ $template ];
	}

	return '';
}

function get_theme_single_position( $stylesheet = null, $template = null ) {
	$stylesheet = empty( $stylesheet ) ? get_stylesheet() : $stylesheet;
	$template   = empty( $template ) ? get_template() : $template;

	$stylesheet = ! empty( $stylesheet ) ? strtolower( $stylesheet ) : $stylesheet;
	$template   = ! empty( $template ) ? strtolower( $template ) : $template;

	$themes = [
		'thegem'      => 'thegem_woocommerce_single_product_left',
		'rehub-theme' => 'rh_woo_after_single_image',
		'woodmart'    => 'woocommerce_single_product_summary',
		'basel'       => 'woocommerce_before_single_product_summary',
	];

	if ( ! empty( $stylesheet ) && isset( $themes[ $stylesheet ] ) ) {
		return $themes[ $stylesheet ];
	}

	if ( ! empty( $template ) && isset( $themes[ $template ] ) ) {
		return $themes[ $template ];
	}

	return '';
}

function get_theme_out_of_image_single_position( $stylesheet = null, $template = null ) {
	$stylesheet = empty( $stylesheet ) ? get_stylesheet() : $stylesheet;
	$template   = empty( $template ) ? get_template() : $template;

	$stylesheet = ! empty( $stylesheet ) ? strtolower( $stylesheet ) : $stylesheet;
	$template   = ! empty( $template ) ? strtolower( $template ) : $template;

	$themes = [];

	if ( ! empty( $stylesheet ) && isset( $themes[ $stylesheet ] ) ) {
		return $themes[ $stylesheet ];
	}

	if ( ! empty( $template ) && isset( $themes[ $template ] ) ) {
		return $themes[ $template ];
	}

	return '';
}

function get_theme_single_container( $stylesheet = null, $template = null ) {
	$stylesheet = empty( $stylesheet ) ? get_stylesheet() : $stylesheet;
	$template   = empty( $template ) ? get_template() : $template;

	$stylesheet = ! empty( $stylesheet ) ? strtolower( $stylesheet ) : $stylesheet;
	$template   = ! empty( $template ) ? strtolower( $template ) : $template;

	$themes = [
		'thegem' => '.product-gallery-slider',
		'eduma' => '.main_product_thumbnai',
	];

	if ( ! empty( $stylesheet ) && isset( $themes[ $stylesheet ] ) ) {
		return $themes[ $stylesheet ];
	}

	if ( ! empty( $template ) && isset( $themes[ $template ] ) ) {
		return $themes[ $template ];
	}

	return '';
}

function allowed_inline_styles( $styles ) {
	$styles[] = 'display';
	return $styles;
}

function get_current_lang() {
	static $current_lang;
	if ( isset( $current_lang ) ) {
		return $current_lang;
	}

	$current_lang = false;
	if ( class_exists( 'SitePress' ) ) {
		$current_lang = apply_filters( 'wpml_current_language', null );
	} elseif ( function_exists( 'pll_current_language' ) ) {
		$current_lang = pll_current_language();
	}

	return $current_lang;
}

function translate( $label, $prop, $badge, $current_lang = '' ) {
	if ( empty( $label ) || empty( $prop ) ) {
		return $label;
	}

	$current_lang = ! empty( $current_lang ) ? $current_lang : get_current_lang();
	if ( ! $current_lang ) {
		return $label;
	}

	if ( isset( $badge->{$prop} ) && ! empty( $badge->$prop[ $current_lang ] ) ) {
		return $badge->$prop[ $current_lang ];
	}

	return $label;
}

function get_saved_percent( $product ) {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}

	if ( ! $product ) {
		return false;
	}

	if ( false !== strpos( $product->get_type(), 'variable' ) ) {
		$prices         = $product->get_variation_prices();
		$max_percentage = 0;
		foreach( $prices['price'] as $key => $price ) {
			// Only on sale variations
			if ( $prices['regular_price'][ $key ] > $price ) {
				$percentage = ( floatval( $prices['regular_price'][ $key ] ) - floatval( $price ) ) / floatval( $prices['regular_price'][ $key ] ) * 100;
				if ( $percentage > $max_percentage ) {
					$max_percentage = $percentage;
				}
			}
		}
		if ( 0 < $max_percentage ) {
			return $max_percentage;
		}
	} else {
		$regular_price = $product->get_regular_price();
		$sale_price    = $product->get_sale_price();
		if ( '' !== $sale_price && $sale_price < $regular_price ) {
			return ( floatval( $regular_price ) - floatval( $sale_price ) ) / floatval( $regular_price ) * 100;
		}
	}

	return false;
}

function get_saved_price( $product ) {
	// Calculate saved price amount from product sale and regular price.
	$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
	if ( ! $product ) {
		return false;
	}

	if ( ! $product->is_on_sale() ) {
		return false;
	}

	if ( false !== strpos( $product->get_type(), 'variable' ) ) {
		$prices     = $product->get_variation_prices();
		$max_amount = 0;
		foreach( $prices['price'] as $key => $price ) {
			// Only on sale variations
			if ( $prices['regular_price'][ $key ] > $price ) {
				$amount = floatval( $prices['regular_price'][ $key ] ) - floatval( $price );
				if ( $amount > $max_amount ) {
					$max_amount = $amount;
				}
			}
		}
		if ( 0 < $max_amount ) {
			return wc_price( $max_amount );
		}
	} else {
		$regular_price = $product->get_regular_price();
		$sale_price    = $product->get_sale_price();
		if ( '' !== $sale_price && $sale_price < $regular_price ) {
			return wc_price( floatval( $regular_price ) - floatval( $sale_price ) );
		}
	}

	return false;
}

function maybe_get_exact_item_id( $id, $type = 'product' ) {
	if ( ! is_numeric( $id ) || 0 >= $id ) {
		return absint( $id );
	}

	return apply_filters( 'asnp_wesb_exact_item_id', absint( $id ), $type );
}

function maybe_get_exact_category_id( $id ) {
	return maybe_get_exact_item_id( $id, 'product_cat' );
}

function maybe_get_exact_tag_id( $id ) {
	return maybe_get_exact_item_id( $id, 'product_tag' );
}

function maybe_get_exact_product( $product ) {
	if ( ! $product ) {
		return $product;
	}

	if ( $product instanceof \WC_Product ) {
		return apply_filters( 'asnp_wesb_exact_product', $product );
	} elseif ( is_numeric( $product ) ) {
		return maybe_get_exact_item_id( $product, 'product' );
	}

	return $product;
}

function maybe_get_exact_attribute_id( $attribute ) {
	if ( empty( $attribute ) ) {
		return $attribute;
	}

	if ( ! empty( $attribute ) ) {
		$attribute = explode( ',', $attribute );
		if (
			2 === count( $attribute ) &&
			! empty( $attribute[0] ) &&
			! empty( $attribute[1] ) &&
			is_numeric( $attribute[1] ) &&
			0 < (int) $attribute[1]
		) {
			return maybe_get_exact_item_id( (int) $attribute[1], $attribute[0] );
		}
	}

	return (int) $attribute;
}

function add_custom_hooks( $custom_hooks, $callback ) {
	if ( empty( $custom_hooks ) || ! strlen( trim( $custom_hooks ) ) ) {
		return;
	}

	if ( ! $callback || ! is_callable( $callback ) ) {
		return;
	}

	$custom_hooks = array_map( 'trim', explode( ',', trim( $custom_hooks ) ) );
	foreach ( $custom_hooks as $hook ) {
		add_action( $hook, $callback, apply_filters( 'asnp_wesb_custom_hook_priority', 99, $hook ) );
	}
}


function localize_timer_badge( $badge ) {
	if ( ! $badge ) {
		return;
	}

	if ( empty( $badge->selectedDateTo ) && empty( $badge->evergreen ) ) {
		return;
	}

	$assets = get_plugin()->container()->get( Assets::class );
	if ( ! $assets ) {
		return;
	}

	if ( $assets->has_timer( (int) $badge->id ) ) {
		return;
	}

	$now = current_time( 'timestamp' );
	$timer = [
		'id'                    => absint( $badge->id ),
		'dateFrom'              => ! empty( $badge->selectedDateFrom ) ? sanitize_text_field( $badge->selectedDateFrom ) : '',
		'dateTo'                => ! empty( $badge->selectedDateTo ) ? sanitize_text_field( $badge->selectedDateTo ) : '',
		'remainingTimeProgress' => ( $now - strtotime( $badge->selectedDateFrom, $now ) ) * 1000,
		'remainingTime'         => ! empty( $badge->selectedDateTo ) ? ( strtotime( $badge->selectedDateTo, $now ) - $now ) * 1000 : 0,
        'evergreen'             => ! empty( $badge->evergreen ) ? absint( $badge->evergreen ) * 60 * 1000 : 0,
        'timerMode'             => ! empty( $badge->timerMode ) ? sanitize_text_field( $badge->timerMode ) : 'fromToDate',
        'evergreenOption'       => ! empty( $badge->evergreenOption ) ? sanitize_text_field( $badge->evergreenOption ) : 'endClose',
    ];

    if ( $timer['remainingTime'] > 0 || $timer['evergreen'] > 0 ) {
		$assets->add_timer( (object) $timer );
	}
}

function get_product_image_src( $product, $size = 'woocommerce_single', $placeholder = true ) {
	$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
	if ( ! $product ) {
		return '';
	}

	$src = '';
	if ( $product->get_image_id() ) {
		$image = wp_get_attachment_image_src( $product->get_image_id(), $size );
		$src   = ! empty( $image ) && ! empty( $image[0] ) ? $image[0] : '';
	} elseif ( $product->get_parent_id() ) {
		$parent_product = wc_get_product( $product->get_parent_id() );
		if ( $parent_product ) {
			$src = get_product_image_src( $parent_product, $size );
		}
	}

	if ( empty( $src ) && $placeholder ) {
		$image = wc_placeholder_img_src( $size );
		$src   = ! empty( $image ) && ! empty( $image[0] ) ? $image[0] : '';
	}

	return apply_filters( 'asnp_wesb_get_product_image_src', $src, $product, $size, $placeholder );
}