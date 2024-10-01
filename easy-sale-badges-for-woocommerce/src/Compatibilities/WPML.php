<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Compatibilities;

defined( 'ABSPATH' ) || exit;

class WPML {

    public static function init() {
        add_filter( 'asnp_wesb_exact_item_id', [ __CLASS__, 'exact_item_id' ], 100, 2 );
        add_filter( 'asnp_wesb_exact_product', [ __CLASS__, 'exact_product' ], 100, 2 );
    }

    public static function exact_item_id( $id, $type ) {
        return apply_filters( 'wpml_object_id', $id, $type, true );
    }

    public static function exact_product( $product ) {
        if ( ! $product instanceof \WC_Product ) {
            return $product;
        }

        return wc_get_product( static::exact_item_id( $product->get_id(), 'product' ) );
    }

}
