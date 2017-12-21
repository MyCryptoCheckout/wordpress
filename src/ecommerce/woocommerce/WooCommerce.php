<?php

namespace mycryptocheckout\ecommerce\woocommerce;

/**
	@brief		Handle checkouts in WooCommerce.
	@since		2017-12-08 16:30:20
**/
class WooCommerce
	extends \plainview\sdk_mcc\wordpress\base
{
	/**
		@brief		Init!
		@since		2017-12-07 19:34:05
	**/
	public function _construct()
	{
		$this->add_filter( 'woocommerce_payment_gateways' );
	}

	/**
		@brief		Calculate the final price of this purchase, with markup.
		@since		2017-12-14 17:00:15
	**/
	public static function markup_total( $amount )
	{
		$markup_amount = MyCryptoCheckout()->get_site_option( 'markup_amount' );
		$amount += $markup_amount;

		$markup_percent = MyCryptoCheckout()->get_site_option( 'markup_percent' );
		$amount = $amount * ( 1 + ( $markup_percent / 100 ) );

		return $amount;
	}

	/**
		@brief		woocommerce_payment_gateways
		@since		2017-12-08 16:31:34
	**/
	public function woocommerce_payment_gateways( $gateways )
	{
		require_once( __DIR__ . '/WC_Gateway_MyCryptoCheckout.php' );
		$gateways []= 'WC_Gateway_MyCryptoCheckout';
		return $gateways;
	}
}
