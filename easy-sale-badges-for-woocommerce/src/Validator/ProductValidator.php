<?php

namespace AsanaPlugins\WooCommerce\SaleBadges\Validator;

defined( 'ABSPATH' ) || exit;

if ( class_exists( '\AsanaPlugins\WooCommerce\SaleBadgesPro\Validator\ProductValidator' ) ) {
	class ProductValidator extends \AsanaPlugins\WooCommerce\SaleBadgesPro\Validator\ProductValidator {}
} else {
	class ProductValidator extends BaseProductValidator {}
}
