<?php

namespace mycryptocheckout\actions;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
	@brief		Action for when WooCommerce is busy creating the order.
	@details	The order has not been saved to disk yet, so the meta can still be modified.
	@since		2019-08-05 21:35:09
**/
class woocommerce_create_order
	extends action
{
	/**
		@brief		IN: The WooCommerce order.
		@since		2019-08-05 21:34:27
	**/
	public $order;

	/**
		@brief		IN: The MCC API Payment object.
		@since		2019-08-05 21:34:36
	**/
	public $payment;
}
