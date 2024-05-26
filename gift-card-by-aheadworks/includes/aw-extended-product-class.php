<?php 
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Aw_Product_Gift_Card extends WC_Product {
	/**
	 * __construct function.
	 *
	 * @param mixed $product
	 */
	public function __construct( $product ) {
		$this->product_type = 'gift_card_virtual'; // Deprecated as of WC3.0 see get_type() method
		parent::__construct( $product );
	}

	 /**
	 * Get internal type.
	 * Needed for WooCommerce 3.0 Compatibility
	  *
	 * @return string
	 */
	public function get_type() {
		return 'gift_card_virtual';
	}
}
