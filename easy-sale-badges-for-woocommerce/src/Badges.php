<?php

namespace AsanaPlugins\WooCommerce\SaleBadges;

defined( 'ABSPATH' ) || exit;

use AsanaPlugins\WooCommerce\SaleBadges\Validator\ProductValidator;
use AsanaPlugins\WooCommerce\SaleBadges\Validator\DateTimeValidator;
use function AsanaPlugins\WooCommerce\SaleBadges\Helpers\Badges\output_badges;
use function AsanaPlugins\WooCommerce\SaleBadges\Helpers\Badges\get_dynamic_style;

class Badges {

	protected $badges;

	protected $valid_badges;

	public function __construct( array $badges ) {
		$this->badges = $badges;
	}

	public function get_badges() {
		return $this->badges;
	}

	public function set_badges( array $badges ) {
		$this->badges = $badges;
	}

	public function display_badges( $product, $hide = false, $return = false, $out_of_image = false ) {
		$badges = $this->get_valid_badges( $product );
		if ( empty( $badges ) ) {
			return '';
		}

		return output_badges( $badges, $hide, $return, $out_of_image );
	}

	public function get_valid_badges( $product ) {
		$product = is_numeric( $product ) ? $product : $product->get_id();
		if ( ! $product ) {
			return [];
		}

		if ( isset( $this->valid_badges[ $product ] ) ) {
			return $this->valid_badges[ $product ];
		}

		if ( empty( $this->badges ) ) {
			return [];
		}

		$badges = [];
		foreach ( $this->badges as $badge ) {
			if ( ! ProductValidator::valid_product( $badge, $product ) ) {
				continue;
			}

			// Validate schedule if exists.
			if (
				! empty( $badge->schedule ) &&
				! DateTimeValidator::is_valid_date_times( $badge->schedule )
			) {
				continue;
			}

			$badges[] = $badge;
		}

		$this->valid_badges[ $product ] = $badges;

		return $badges;
	}

	public function add_dynamic_styles() {
		if ( empty( $this->badges ) ) {
			return;
		}

		foreach ( $this->badges as $badge ) {
			$out_of_image = false;
			if ( isset( $badge->cssLabelPosition ) && 'outOfImage' === $badge->cssLabelPosition ) {
				if ( isset( $badge->useTimerBadge ) && 1 == $badge->useTimerBadge ) {
					$out_of_image = true;
				} elseif (
					( ! isset( $badge->imgbadge ) || 0 == $badge->imgbadge ) &&
					( ! isset( $badge->imgbadgeAdv ) || 0 == $badge->imgbadgeAdv )
				) {
					$out_of_image = true;
				}
			}

			$style = get_dynamic_style( $badge, false, $out_of_image );
			if ( ! empty( $style ) ) {
				add_custom_style( $style, $badge );
			}
		}
	}

}
