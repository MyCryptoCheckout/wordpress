<?php

namespace mycryptocheckout\ecommerce\woocommerce;

use Exception;

/**
	@brief		Handle checkouts in WooCommerce.
	@since		2017-12-08 16:30:20
**/
class WooCommerce
	extends \plainview\sdk_mcc\wordpress\base
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
		$this->add_action( 'mycryptocheckout_payment_complete' );
		$this->add_action( 'mycryptocheckout_woocommerce_send_payment' );
		$this->add_action( 'woocommerce_checkout_update_order_meta' );
		$this->add_filter( 'woocommerce_payment_gateways' );
	}

	/**
		@brief		Generate a Payment class from an order.
		@since		2017-12-21 23:47:17
	**/
	public function generate_payment_from_order( $order_id )
	{
		$order = wc_get_order( $order_id );
		$payment = new \mycryptocheckout\api\Payment();
		$payment->amount = $order->get_meta( '_mcc_amount' );
		$payment->confirmations = $order->get_meta( '_mcc_confirmations' );
		$payment->created_at = $order->get_meta( '_mcc_created_at' );
		$payment->currency_id = $order->get_meta( '_mcc_currency_id' );
		$payment->from = $order->get_meta( '_mcc_from' );
		$payment->to = $order->get_meta( '_mcc_to' );

		// If we are on a network, then note down the site ID.
		if ( $this->is_network )
			$payment->data()->set( 'site_id', get_current_blog_id() );

		return $payment;
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
		@brief		Hourly cron.
		@since		2017-12-24 12:10:14
	**/
	public function mycryptocheckout_hourly()
	{
		$this->send_unsent_payments();
	}

	/**
		@brief		mycryptocheckout_payment_complete
		@since		2017-12-26 10:17:13
	**/
	public function mycryptocheckout_payment_complete( $payment )
	{
		$switched_blog = 0;
		if ( isset( $payment->data ) )
		{
			$data = json_decode( $payment->data );
			if ( $data )
			{
				if ( isset( $data->site_id ) )
				{
					$switched_blog = $data->site_id;
					switch_to_blog( $switched_blog );
				}
			}
		}

		// Find the payment with this ID.
		global $wpdb;
		$query = sprintf( "SELECT `post_id` FROM `%s` WHERE `meta_key` = '_mcc_payment_id' AND `meta_value` = '%d'",
			$wpdb->postmeta,
			$payment->payment_id
		);
		$results = $wpdb->get_col( $query );
		foreach( $results as $order_id )
		{
			$order = wc_get_order( $order_id );
			$order->payment_complete( $payment->transaction_id );
		}

		if ( $switched_blog > 0 )
			restore_current_blog();
	}

	/**
		@brief		Attempt to send a payment to the API server.
		@since		2017-12-22 08:00:21
	**/
	public function mycryptocheckout_woocommerce_send_payment( $order_id )
	{
		$order = wc_get_order( $order_id );
		$attempts = intval( $order->get_meta( '_mcc_attempts' ) );

		try
		{
			$payment = $this->generate_payment_from_order( $order_id );
			$payment_id = MyCryptoCheckout()->api()->payments()->add( $payment );
			$order->update_meta_data( '_mcc_payment_id', $payment_id );
			MyCryptoCheckout()->debug( 'Payment for order %d has been added as payment #%d.', $order_id, $payment_id );
		}
		catch ( Exception $e )
		{
			$attempts++;
			$order->update_meta_data( '_mcc_attempts', $attempts );
			MyCryptoCheckout()->debug( 'Failure #%d trying to send the payment for order %d. %s', $attempts, $order_id, $e->getMessage() );
			if ( $attempts > 48 )	// 48 hours, since this is usually run on the hourly cron.
			{
				// TODO: Give up and inform the admin of the failure.
				MyCryptoCheckout()->debug( 'We have now given up on trying to send the payment for order %d.', $order_id );
				$order->update_meta_data( '_mcc_payment_id', -1 );
			}
		}

		// We save it here, because either we got a payment ID or we updated the attempts.
		$order->save();
	}

	/**
		@brief		send_unsent_payments
		@since		2017-12-24 12:11:03
	**/
	public function send_unsent_payments()
	{
		// Find all orders in the database that do not have a payment ID.
		global $wpdb;
		$query = sprintf( "SELECT `post_id` FROM `%s` WHERE `meta_key` = '_mcc_payment_id' AND `meta_value` = '0'",
			$wpdb->postmeta
		);
		$results = $wpdb->get_col( $query );
		if ( count( $results ) < 1 )
			return;
		MyCryptoCheckout()->debug( 'Unsent payments: %s', implode( ', ', $results ) );
		foreach( $results as $order_id )
			$this->mycryptocheckout_woocommerce_send_payment( $order_id );
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
		do_action( 'mycryptocheckout_woocommerce_send_payment', $order_id );
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
