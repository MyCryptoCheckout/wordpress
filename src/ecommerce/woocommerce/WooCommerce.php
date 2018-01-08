<?php

namespace mycryptocheckout\ecommerce\woocommerce;

use Exception;

/**
	@brief		Handle checkouts in WooCommerce.
	@since		2017-12-08 16:30:20
**/
class WooCommerce
	extends \mycryptocheckout\ecommerce\Ecommerce
{
	/**
		@brief		The ID of the gateway.
		@since		2017-12-08 16:45:27
	**/
	public static $gateway_id = 'mycryptocheckout';

	/**
		@brief		Init!
		@since		2017-12-07 19:34:05
	**/
	public function _construct()
	{
		$this->add_action( 'mycryptocheckout_hourly' );
		$this->add_action( 'mycryptocheckout_cancel_payment' );
		$this->add_action( 'mycryptocheckout_payment_complete' );
		$this->add_action( 'woocommerce_checkout_update_order_meta' );
		$this->add_filter( 'woocommerce_payment_gateways' );
	}

	/**
		@brief		Is MCC available for payments on this WC installation?
		@return		True if avaiable, else an exception containing the reason why it is not.
		@since		2017-12-23 08:56:28
	**/
	public function is_available_for_payment()
	{
		$account = MyCryptoCheckout()->api()->account();
		$account->is_available_for_payment();

		// We need to be able to convert this currency.
		$wc_currency = get_woocommerce_currency();
		if ( ! $account->get_physical_exchange_rate( $wc_currency ) )
			throw new Exception( sprintf( 'Your WooCommerce installation is using an unknown currency: %s', $wc_currency ) );

		return true;
	}

	/**
		@brief		Hourly cron.
		@since		2017-12-24 12:10:14
	**/
	public function mycryptocheckout_hourly()
	{
		if ( ! function_exists( 'WC' ) )
			return;
		MyCryptoCheckout()->api()->payments()->send_unsent_payments();
	}

	/**
		@brief		Payment was abanadoned.
		@since		2018-01-06 15:59:11
	**/
	public function mycryptocheckout_cancel_payment( $payment )
	{
		$this->do_with_payment( $payment, function( $order_id )
		{
			if ( ! function_exists( 'WC' ) )
				return;
			$order = wc_get_order( $order_id );
			if ( ! $order )
				return;
			MyCryptoCheckout()->debug( 'Marking WC payment %s on blog %d as cancelled.', $order_id, get_current_blog_id() );
			update_post_meta( $order_id, '_mcc_payment_id', -1 );
			$order->update_status( 'cancelled', 'Payment timed out.' );
		} );
	}

	/**
		@brief		mycryptocheckout_payment_complete
		@since		2017-12-26 10:17:13
	**/
	public function mycryptocheckout_payment_complete( $payment )
	{
		$this->do_with_payment( $payment, function( $order_id, $payment )
		{
			if ( ! function_exists( 'WC' ) )
				return;
			$order = wc_get_order( $order_id );
			if ( ! $order )
				return;
			MyCryptoCheckout()->debug( 'Marking WC payment %s on blog %d as complete.', $order_id, get_current_blog_id() );
			$order->payment_complete( $payment->transaction_id );
		} );
	}

	/**
		@brief		Maybe send this order to the API.
		@since		2017-12-25 16:21:06
	**/
	public function woocommerce_checkout_update_order_meta( $order_id )
	{
		$order = wc_get_order( $order_id );
		$payment_method = $order->get_payment_method();
		// This must be a MCC checkout.
		if ( $payment_method != static::$gateway_id )
			return;
		do_action( 'mycryptocheckout_send_payment', $order_id );
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
