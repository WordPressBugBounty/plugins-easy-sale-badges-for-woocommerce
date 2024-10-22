<?php

namespace AsanaPlugins\WooCommerce\SaleBadges;

defined( 'ABSPATH' ) || exit;

class Placeholder {

    public static function init() {
        add_filter( 'asnp_wesb_css_badge_label', array( __CLASS__, 'css_label' ), 10, 3 );
		add_filter( 'asnp_wesb_advanced_badge_label', array( __CLASS__, 'advanced_label' ), 10, 3 );
    }

	public static function css_label( $label, $badge, $product ) {
		if ( empty( $label ) || ! $badge ) {
			return $label;
		}

		if ( ! $product ) {
			return $label;
		}

		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;

		if ( ! empty( $badge->percentageDiscount ) ) {
			$percent = static::percentage_label( $label, $product );
			if ( ! empty( $percent ) ) {
				return $percent;
			}
		}

		return static::replace( $label, $product );
	}

	public static function advanced_label( $label, $badge, $product ) {
		if ( empty( $label ) || ! $badge ) {
			return $label;
		}

		if ( ! $product ) {
			return $label;
		}

		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;

		if ( ! empty( $badge->percentageDiscountAdv ) ) {
			$percent = static::percentage_label_adv( $label, $product );
			if ( ! empty( $percent ) ) {
				return $percent;
			}
		}

		return static::replace( $label, $product );
	}

	public static function get_placeholders() {
		return apply_filters( 'asnp_wesb_placeholders', [
            '{regular_price}' => 'regular_price',
            '{sale_price}' => 'sale_price',
            '{price}' => 'price',
            '{saved_price}' => 'saved_price',
            '{saved_percent}' => 'saved_percent',
            '{sale_ends}' => 'sale_ends',
            '{currency}' => 'currency',
            '{qty}' => 'quantity',
            '{br}' => 'br',
            '{sku}' => 'sku',
        ] );
	}

    public static function replace( $label, $product ) {
		if ( empty( $label ) || ! $product ) {
			return $label;
		}

        $placeholders = static::get_placeholders();
		if ( empty( $placeholders ) ) {
            return $label;
        }

        foreach ( $placeholders as $placeholder => $method ) {
            if ( false !== stripos( $label, $placeholder ) ) {
                if ( is_callable( [ __CLASS__, $method ] ) ) {
                    $label = static::$method( $label, $product );
                } else {
                    $label = apply_filters( 'asnp_wesb_label_placeholder_replace_' . $method, $label, $product );
                }
            }
        }

        return $label;
    }

    public static function price( $label, $product ) {
		return str_ireplace( '{price}', wc_price( wc_get_price_to_display( $product ) ), $label );
    }

    public static function regular_price( $label, $product ) {
		return str_ireplace( '{regular_price}', wc_price( wc_get_price_to_display( $product, [ 'price' => $product->get_regular_price() ] ) ), $label );
    }

    public static function sale_price( $label, $product ) {
		return str_ireplace( '{sale_price}', wc_price( wc_get_price_to_display( $product ) ), $label );
    }

	public static function saved_price( $label, $product ) {
		$price = get_saved_price( $product );
		if ( empty( $price ) ) {
			return str_ireplace( '{saved_price}', '', $label );
		}

		$prefix = '';
		if ( (int) get_plugin()->settings->get_setting( 'negativeSign', 1 ) ) {
			$prefix = apply_filters( 'asnp_wesb_sale_flash_price_discount_prefix', '<span class="asnp-wesb-sale-flash-prefix">-</span>' );
		}

		$price = apply_filters(
			'asnp_wesb_saved_price_label',
			$prefix . $price,
			$price,
			$label,
			$product
		);

		return str_ireplace( '{saved_price}', $price, $label );
	}

	public static function saved_percent( $label, $product ) {
		$percent = get_saved_percent( $product );
		if ( empty( $percent ) ) {
			return str_ireplace( '{saved_percent}', '', $label );
		}

		$prefix = '';
		if ( (int) get_plugin()->settings->get_setting( 'negativeSign', 1 ) ) {
			$prefix = apply_filters( 'asnp_wesb_sale_flash_percentage_discount_prefix', '<span class="asnp-wesb-sale-flash-prefix">-</span>' );
		}

		$percent = apply_filters(
			'asnp_wesb_saved_percent_label',
			$prefix . round( $percent ) .
			apply_filters( 'asnp_wesb_sale_flash_percentage_symbol', '<span class="asnp-wesb-sale-flash-percentage-symbol">%</span>' ),
			$percent,
			$label,
			$product
		);

		return str_ireplace( '{saved_percent}', $percent, $label );
	}

	public static function sale_ends( $label, $product ) {
		if ( $product->is_on_sale() ) {
			// calculate days until to sale ends.
			if ( $product->get_date_on_sale_to() && $product->get_date_on_sale_to()->getTimestamp() > current_time( 'timestamp' ) ) {
				$days = round( ( $product->get_date_on_sale_to()->getTimestamp() - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
				if ( 1 == $days ) {
					$days = sprintf( __( '%d day', 'easy-sale-badges-for-woocommerce' ), 1 );
				} elseif ( 1 < $days ) {
					$days = sprintf( __( '%d days', 'easy-sale-badges-for-woocommerce' ), $days );
				} else {
					$days = '';
				}

				return str_ireplace( '{sale_ends}', $days, $label );
			}
		}

		return str_ireplace( '{sale_ends}', '', $label );
	}

	public static function currency( $label, $product ) {
		return str_ireplace( '{currency}', get_woocommerce_currency_symbol(), $label );
	}

	public static function quantity( $label, $product ) {
		$max_quantity = $product->get_max_purchase_quantity();
		$max_quantity = 0 < $max_quantity ? $max_quantity : '';
		$label = str_ireplace( '{qty}', $max_quantity, $label );
		return $label;
	}

	public static function br( $label, $product ) {
		return str_ireplace( '{br}', '<br>', $label );
	}

	public static function sku( $label, $product ) {
		return str_ireplace( '{sku}', $product->get_sku(), $label );
	}

	public static function percentage_label( $label, $product ) {
		$percent = get_saved_percent( $product );
		if ( empty( $percent ) ) {
			return false;
		}

		$prefix = '';
		if ( (int) get_plugin()->settings->get_setting( 'negativeSign', 1 ) ) {
			$prefix = apply_filters( 'asnp_wesb_sale_flash_percentage_discount_prefix', '<span class="asnp-wesb-sale-flash-prefix">-</span>' );
		}

		return apply_filters(
			'asnp_wesb_saved_percent_label',
			$prefix . round( $percent ) .
			apply_filters( 'asnp_wesb_sale_flash_percentage_symbol', '<span class="asnp-wesb-sale-flash-percentage-symbol">%</span>' ),
			$percent,
			$label,
			$product
		);
	}

	public static function percentage_label_adv( $label, $product ) {
		$percent = get_saved_percent( $product );
		if ( empty( $percent ) ) {
			return false;
		}

		$prefix = '';
		if ( (int) get_plugin()->settings->get_setting( 'negativeSign', 1 ) ) {
			$prefix = '-';
		}

		return $prefix . round( $percent ) . '%';
	}

}
