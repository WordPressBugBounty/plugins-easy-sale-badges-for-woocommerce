<?php

namespace AsanaPlugins\WooCommerce\SaleBadges;

defined( 'ABSPATH' ) || exit;

use function AsanaPlugins\WooCommerce\SaleBadges\add_custom_hooks;
use function AsanaPlugins\WooCommerce\SaleBadges\is_pro_active;

class Hooks {

	public static function init() {
		if ( get_plugin()->is_request( 'frontend' ) ) {
			if ( (int) get_plugin()->settings->get_setting( 'showBadgeProductPage', 1 ) ) {
				static::single_hooks();
			}
			static::loop_hooks();

			$quick_view = get_plugin()->settings->get_store_features_setting( 'quick_view', 'quick_view', 0 );
			if ( $quick_view ) {
				static::loop_hooks_quick_view();
			}

			if ( (int) get_plugin()->settings->get_setting( 'hideWooCommerceBadges', 0 ) ) {
				add_filter( 'woocommerce_sale_flash', '__return_empty_string', 9999999 );
				add_custom_style( '.onsale{display:none !important;}' );
			}
		}
	}

	public static function single_hooks() {
		static::single_out_of_image_hooks();

		$custom_hooks = static::single_custom_hooks();
		if ( ! empty( $custom_hooks ) ) {
			return;
		}

		$single_position = get_theme_single_position();
		if ( empty( $single_position ) ) {
			$single_position = get_plugin()->settings->get_setting( 'singlePosition', 'before_single_item_images' );
		}

		if ( empty( $single_position ) || 'none' === $single_position ) {
			return;
		}

		switch ( $single_position ) {
			case 'before_single_item_images':
				$priority = has_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images' );
				if ( $priority ) {
					add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), 19 );
				}
				break;

			case 'after_single_item_images':
				$priority = has_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images' );
				if ( $priority ) {
					add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), 21 );
				}
				break;

			case 'before_single_item_title':
				$priority = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title' );
				if ( $priority ) {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), 4 );
				}
				break;

			case 'after_single_item_title':
				$priority = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title' );
				if ( $priority ) {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), 6 );
				}
				break;

			case 'before_single_item_price':
				$priority = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price' );
				if ( $priority ) {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), 9 );
				}
				break;

			case 'after_single_item_price':
				$priority = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price' );
				if ( $priority ) {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_sale_badge' ), 11 );
				}
				break;

			case 'post_thumbnail_html':
				add_filter( 'post_thumbnail_html', array( __CLASS__, 'post_thumbnail_html' ), 10, 4 );
				break;

			default:
				add_action( $single_position, array( __CLASS__, 'single_dispaly_sale_badge' ), 99 );
				break;
		}
	}

	public static function single_custom_hooks() {
		$custom_hooks = get_plugin()->settings->get_setting( 'singleCustomHooks', '' );
		$custom_hooks = apply_filters( 'asnp_wesb_single_custom_hooks', $custom_hooks );
		$custom_hooks = trim( $custom_hooks );

		add_custom_hooks( $custom_hooks, array( __CLASS__, 'single_dispaly_sale_badge' ) );

		return $custom_hooks;
	}

	public static function single_out_of_image_hooks() {
		$single_position = get_plugin()->settings->get_setting( 'singleOutOfImagePosition', 'after_single_item_title' );

		if ( empty( $single_position ) || 'none' === $single_position ) {
			return;
		}

		switch ( $single_position ) {
			case 'before_single_item_images':
				$priority = has_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images' );
				if ( $priority ) {
					add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), 19 );
				}
				break;

			case 'after_single_item_images':
				$priority = has_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images' );
				if ( $priority ) {
					add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), 21 );
				}
				break;

			case 'before_single_item_title':
				$priority = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title' );
				if ( $priority ) {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), 4 );
				}
				break;

			case 'after_single_item_title':
				$priority = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title' );
				if ( $priority ) {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), 6 );
				}
				break;

			case 'before_single_item_price':
				$priority = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price' );
				if ( $priority ) {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), 9 );
				}
				break;

			case 'after_single_item_price':
				$priority = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price' );
				if ( $priority ) {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), 11 );
				}
				break;

			case 'before_add_to_cart_button':
			case 'after_add_to_cart_button':
				$priority = has_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart' );
				if ( 'before_add_to_cart_button' === $single_position ) {
					$priority ?
						add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), $priority - 1 ) :
						add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), 29 );
				} elseif ( 'after_add_to_cart_button' === $single_position ) {
					$priority ?
						add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), $priority + 1 ) :
						add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), 31 );
				}
				break;
			case 'before_add_to_cart_form':
				add_action( 'woocommerce_before_add_to_cart_form', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ) );
				break;

			case 'after_add_to_cart_form':
				add_action( 'woocommerce_after_add_to_cart_form', array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ) );
				break;

			default:
				add_action( $single_position, array( __CLASS__, 'single_dispaly_out_of_image_sale_badge' ), 99 );
				break;
		}
	}

	public static function loop_hooks() {
		static::loop_out_of_image_hooks();

		$custom_hooks = static::loop_custom_hooks();
		if ( ! empty( $custom_hooks ) ) {
			return;
		}

		$loop_position = get_theme_loop_position();
		if ( empty( $loop_position ) ) {
			$loop_position = get_plugin()->settings->get_setting( 'loopPosition', 'woocommerce_product_get_image' );
		}

		if ( empty( $loop_position ) || 'none' === $loop_position ) {
			return;
		}

		switch ( $loop_position ) {
			case 'before_shop_loop_item_thumbnail':
				$priority = has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' );
				if ( $priority ) {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), 9 );
				}
				break;

			case 'after_shop_loop_item_thumbnail':
				$priority = has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' );
				if ( $priority ) {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), 11 );
				}
				break;

			case 'before_shop_loop_item_title':
				$priority = has_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title' );
				if ( $priority ) {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), 9 );
				}
				break;

			case 'after_shop_loop_item_title':
				$priority = has_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title' );
				if ( $priority ) {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), 10 );
				}
				break;

			case 'before_shop_loop_item_rating':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), 4 );
				}
				break;

			case 'after_shop_loop_item_rating':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), 6 );
				}
				break;

			case 'before_shop_loop_item_price':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), 9 );
				}
				break;

			case 'after_shop_loop_item_price':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_sale_badge' ), 11 );
				}
				break;

			case 'after_shop_loop_item':
				add_action( "after_shop_loop_item", array( __CLASS__, 'display_sale_badge' ), 99 );
				break;

			case 'shop_loop':
				add_action( "shop_loop", array( __CLASS__, 'display_sale_badge' ), 99 );
				break;

			case 'woocommerce_product_get_image':
				add_filter( 'woocommerce_product_get_image', array( __CLASS__, 'woocommerce_product_get_image' ), 10, 2 );
				break;

			case 'post_thumbnail_html':
				add_filter( 'post_thumbnail_html', array( __CLASS__, 'post_thumbnail_html' ), 10, 4 );
				break;

			default:
				add_action( $loop_position, array( __CLASS__, 'display_sale_badge' ), 99 );
				break;
		}
	}

	public static function loop_hooks_quick_view() {
		$position = get_plugin()->settings->get_store_features_setting( 'quick_view', 'quick_view_position', 'out_of_image' );

		$custom_hooks_qv = static::loop_custom_hooks_quick_view();
		if ( ! empty( $custom_hooks_qv ) ) {
			return;
		}

		if ( $position == 'out_of_image' ) {
			return static::loop_out_of_image_hooks_quick_view();
		}

		if ( empty( $qv_position ) ) {
			$qv_position = get_plugin()->settings->get_store_features_setting( 'quick_view', 'quick_view_on_image', 'woocommerce_product_get_image' );
		}

		if ( empty( $qv_position ) || 'none' === $qv_position ) {
			return;
		}

		switch ( $qv_position ) {
			case 'before_shop_loop_item_thumbnail':
				$priority = has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' );
				if ( $priority ) {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 9 );
				}
				break;

			case 'after_shop_loop_item_thumbnail':
				$priority = has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' );
				if ( $priority ) {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 11 );
				}
				break;

			case 'before_shop_loop_item_title':
				$priority = has_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title' );
				if ( $priority ) {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 9 );
				}
				break;

			case 'after_shop_loop_item_title':
				$priority = has_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title' );
				if ( $priority ) {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 10 );
				}
				break;

			case 'before_shop_loop_item_rating':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 4 );
				}
				break;

			case 'after_shop_loop_item_rating':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 6 );
				}
				break;

			case 'before_shop_loop_item_price':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 9 );
				}
				break;

			case 'after_shop_loop_item_price':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 11 );
				}
				break;

			case 'woocommerce_product_get_image':
				add_filter( 'woocommerce_product_get_image', array( __CLASS__, 'woocommerce_product_get_image_quick_view' ), 10, 2 );
				break;

			case 'post_thumbnail_html':
				add_filter( 'post_thumbnail_html', array( __CLASS__, 'post_thumbnail_html_qv' ), 10, 4 );
				break;

			default:
				add_action( $qv_position, array( __CLASS__, 'display_quick_view_button' ), 99 );
				break;
		}
	}

	public static function loop_out_of_image_hooks() {
		$loop_position = get_theme_out_of_image_loop_position();

		if ( empty( $loop_position ) ) {
			$loop_position = get_plugin()->settings->get_setting( 'loopOutOfImagePosition', 'after_shop_loop_item_title' );
		}

		if ( empty( $loop_position ) || 'none' === $loop_position ) {
			return;
		}

		switch ( $loop_position ) {
			case 'before_shop_loop_item_thumbnail':
				$priority = has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' );
				if ( $priority ) {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), 9 );
				}
				break;

			case 'after_shop_loop_item_thumbnail':
				$priority = has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' );
				if ( $priority ) {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), 11 );
				}
				break;

			case 'before_shop_loop_item_title':
				$priority = has_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title' );
				if ( $priority ) {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), 9 );
				}
				break;

			case 'after_shop_loop_item_title':
				$priority = has_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title' );
				if ( $priority ) {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), 10 );
				}
				break;

			case 'before_shop_loop_item_rating':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), 4 );
				}
				break;

			case 'after_shop_loop_item_rating':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), 6 );
				}
				break;

			case 'before_shop_loop_item_price':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), 9 );
				}
				break;

			case 'after_shop_loop_item_price':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_out_of_image_sale_badge' ), 11 );
				}
				break;

			case 'after_shop_loop_item':
				add_action( "after_shop_loop_item", array( __CLASS__, 'display_out_of_image_sale_badge' ), 99 );
				break;

			case 'shop_loop':
				add_action( "shop_loop", array( __CLASS__, 'display_out_of_image_sale_badge' ), 99 );
				break;

			default:
				add_action( $loop_position, array( __CLASS__, 'display_out_of_image_sale_badge' ), 99 );
				break;
		}
	}

	public static function loop_out_of_image_hooks_quick_view() {
		if ( empty( $qv_position ) ) {
			$qv_position = get_plugin()->settings->get_store_features_setting( 'quick_view', 'quick_view_out_of_image', 'woocommerce_after_shop_loop_item' );
		}

		if ( empty( $qv_position ) || 'none' === $qv_position ) {
			return;
		}

		switch ( $qv_position ) {
			case 'before_shop_loop_item_thumbnail':
				$priority = has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' );
				if ( $priority ) {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 9 );
				}
				break;

			case 'after_shop_loop_item_thumbnail':
				$priority = has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail' );
				if ( $priority ) {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 11 );
				}
				break;

			case 'before_shop_loop_item_title':
				$priority = has_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title' );
				if ( $priority ) {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 9 );
				}
				break;

			case 'after_shop_loop_item_title':
				$priority = has_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title' );
				if ( $priority ) {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 10 );
				}
				break;

			case 'before_shop_loop_item_rating':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 4 );
				}
				break;

			case 'after_shop_loop_item_rating':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 6 );
				}
				break;

			case 'before_shop_loop_item_price':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority - 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 9 );
				}
				break;

			case 'after_shop_loop_item_price':
				$priority = has_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price' );
				if ( $priority ) {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), $priority + 1 );
				} else {
					add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'display_quick_view_button' ), 11 );
				}
				break;

			default:
				add_action( $qv_position, array( __CLASS__, 'display_quick_view_button' ), 99 );
				break;
		}
	}

	public static function loop_custom_hooks() {
		$custom_hooks = get_plugin()->settings->get_setting( 'loopCustomHooks', '' );
		$custom_hooks = apply_filters( 'asnp_wesb_loop_custom_hooks', $custom_hooks );
		$custom_hooks = trim( $custom_hooks );

		add_custom_hooks( $custom_hooks, array( __CLASS__, 'display_sale_badge' ) );

		return $custom_hooks;
	}

	public static function loop_custom_hooks_quick_view() {
		$custom_hooks = get_plugin()->settings->get_store_features_setting( 'quick_view', 'loopCustomHooksQuickView', '' );
		$custom_hooks = apply_filters( 'asnp_wesb_loop_custom_hooks_quick_view', $custom_hooks );
		$custom_hooks = trim( $custom_hooks );

		add_custom_hooks( $custom_hooks, array( __CLASS__, 'display_quick_view_button' ) );

		return $custom_hooks;
	}

	public static function single_dispaly_sale_badge() {
		$product = get_current_product();
		if ( ! $product ) {
			return;
		}

		display_sale_badges( $product, true );
	}

	public static function single_dispaly_out_of_image_sale_badge() {
		$product = get_current_product();
		if ( ! $product ) {
			return;
		}

		display_sale_badges( $product, true, false, true );
	}

	public static function display_sale_badge() {
		$product = get_current_product();
		if ( ! $product ) {
			return;
		}

		display_sale_badges( $product );
	}

	public static function display_out_of_image_sale_badge() {
		$product = get_current_product();
		if ( ! $product ) {
			return;
		}

		display_sale_badges( $product, false, false, true );
	}

	public static function woocommerce_product_get_image( $image, $product ) {
		if ( is_admin() && ! is_ajax() ) {
			return $image;
		}

		if ( is_cart() || is_checkout() || is_order_received_page() ) {
			return $image;
		}

		if (
			is_callable( '\WC_Blocks_Utils::has_block_in_page' ) &&
			( \WC_Blocks_Utils::has_block_in_page( get_the_ID(), 'woocommerce/checkout' ) ||
				\WC_Blocks_Utils::has_block_in_page( get_the_ID(), 'woocommerce/cart' ) )
		) {
			return $image;
		}

		// Prevent show badge in mini-cart.
		if ( ! empty( $_GET['wc-ajax'] ) ) {
			return $image;
		}

		if ( is_single() ) {
			global $wp_current_filter;
			if ( ! in_array( 'woocommerce_before_shop_loop_item_title', $wp_current_filter ) ) {
				return $image;
			}
		}

		$badge = display_sale_badges( $product, false, true );
		if ( empty( $badge ) ) {
			return $image;
		}

		if ( false === strpos( $image, '<div' ) ) {
			$image = '<div class="asnp-sale-badge-image-wrapper" style="display:block; position:relative;">' . $image . $badge . '</div>';
		}

		return $image;
	}

	public static function woocommerce_product_get_image_quick_view( $image ) {
		if ( is_admin() && ! is_ajax() ) {
			return $image;
		}

		if ( is_cart() || is_checkout() || is_order_received_page() ) {
			return $image;
		}

		if (
			is_callable( '\WC_Blocks_Utils::has_block_in_page' ) &&
			( \WC_Blocks_Utils::has_block_in_page( get_the_ID(), 'woocommerce/checkout' ) ||
				\WC_Blocks_Utils::has_block_in_page( get_the_ID(), 'woocommerce/cart' ) )
		) {
			return $image;
		}

		if ( ! empty( $_GET['wc-ajax'] ) ) {
			return $image;
		}

		ob_start();
		static::display_quick_view_button();
		$qv = ob_get_clean();

		if ( empty( $qv ) ) {
			return $image;
		}

		$image = '<div class="asnp-wesb-qv-image-wrapper" style="display:block; position:relative;">' . $image . $qv . '</div>';

		return $image;
	}

	public static function post_thumbnail_html( $image, $post_id, $post_thumbnail_id, $size ) {
		if ( 'shop_catalog' !== $size && ! is_product_page() ) {
			return $image;
		}

		if ( is_product_page() ) {
			$product = wc_get_product( $post_id );
			if ( ! is_a( $product, 'WC_Product' ) ) {
				return $image;
			}
		}

		$badge = display_sale_badges( $post_id, false, true );
		if ( empty( $badge ) ) {
			return $image;
		}

		return $image . $badge;
	}

	public static function post_thumbnail_html_qv( $image, $post_id, $post_thumbnail_id, $size ) {
		if ( 'shop_catalog' !== $size && ! is_product_page() ) {
			return $image;
		}

		if ( is_product_page() ) {
			$product = wc_get_product( $post_id );
			if ( ! is_a( $product, 'WC_Product' ) ) {
				return $image;
			}
		}

		ob_start();
		static::display_quick_view_button();
		$qv = ob_get_clean();

		if ( empty( $qv ) ) {
			return $image;
		}

		return $image . $qv;
	}

	public static function display_quick_view_button() {
		$settings = get_plugin()->settings;
		$quick_view_position = $settings->get_store_features_setting( 'quick_view', 'quick_view_position', 'out_of_image' );
		$quick_view_mobile = $settings->get_store_features_setting( 'quick_view', 'quick_view_mobile', 0 );
		$quick_view_text = $settings->get_store_features_setting( 'quick_view', 'quick_view_text', __( 'Quick View', 'easy-sale-badges-for-woocommerce' ) );

		if ( function_exists( 'wp_is_mobile' ) && wp_is_mobile() && ! $quick_view_mobile ) {
			return;
		}

		$product = get_current_product();
		if ( ! $product ) {
			return;
		}

		if ( 'on_image' === $quick_view_position ) {
			if (
				is_pro_active() &&
				function_exists(
					'\AsanaPlugins\WooCommerce\SaleBadgesPro\Helpers\QuickView\display_quick_view_icon'
				)
			) {
				return \AsanaPlugins\WooCommerce\SaleBadgesPro\Helpers\QuickView\display_quick_view_icon(
					$product, $settings
				);
			}
		}

		echo '<a href="#"
			class="button asnp-wesb-quick-view-btn" 
			data-product-id="' . esc_attr( $product->get_id() ) . '"
			>
			' . esc_html( $quick_view_text ) . '
			</a>';
	}

}
