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
		$this->add_action( 'woocommerce_admin_order_data_after_order_details' );
		$this->add_action( 'woocommerce_checkout_create_order', 10, 2 );
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

			// Only cancel is the order is unpaid.
			if ( $order->get_status() != 'on-hold' )
				return MyCryptoCheckout()->debug( 'WC order %d on blog %d is not unpaid. Can not cancel.', $order_id, get_current_blog_id() );

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
		@brief		woocommerce_admin_order_data_after_order_details
		@since		2017-12-14 20:35:48
	**/
	public function woocommerce_admin_order_data_after_order_details( $order )
	{
		if ( $order->get_payment_method() != static::$gateway_id )
			return;

		$amount = $order->get_meta( '_mcc_amount' );

		$r = '';
		$r .= sprintf( '<h3>%s</h3>',
			__( 'MyCryptoCheckout details', 'woocommerce' )
		);

		if ( $order->is_paid() )
			$r .= sprintf( '<p class="form-field form-field-wide">%s</p>',
				// Received 123 BTC to xyzabc
				sprintf( __( 'Received %s&nbsp;%s<br/>to %s', 'mycryptocheckout'),
					$amount,
					$order->get_meta( '_mcc_currency_id' ),
					$order->get_meta( '_mcc_to' )
				)
			);
		else
		{
			$r .= sprintf( '<p class="form-field form-field-wide">%s</p>',
				// Expecting 123 BTC to xyzabc
				sprintf( __( 'Expecting %s&nbsp;%s<br/>to %s', 'mycryptocheckout'),
					$amount,
					$order->get_meta( '_mcc_currency_id' ),
					$order->get_meta( '_mcc_to' )
				)
			);

			$attempts = $order->get_meta( '_mcc_attempts' );
			$payment_id = $order->get_meta( '_mcc_payment_id' );

			if ( $payment_id > 0 )
			{
				if ( $payment_id == 1 )
					$payment_id = __( 'Test', 'mycryptocheckout' );
				$r .= sprintf( '<p class="form-field form-field-wide">%s</p>',
					// Expecting 123 BTC to xyzabc
					sprintf( __( 'MyCryptoCheckout payment ID: %s', 'mycryptocheckout'),
						$payment_id
					)
				);
			}
			else
			{
				if ( $attempts > 0 )
					$r .= sprintf( '<p class="form-field form-field-wide">%s</p>',
						sprintf( __( '%d attempts made to contact the API server.', 'mycryptocheckout'),
							$attempts
						)
					);
			}
		}

		echo $r;
	}

	/**
		@brief		Add the meta fields.
		@since		2017-12-10 21:35:29
	**/
	public function woocommerce_checkout_create_order( $order, $data )
	{
		if ( $order->get_payment_method() != static::$gateway_id )
			return;

		$currency_id = sanitize_text_field( $_POST[ 'mcc_currency_id' ] );

		// All of the below is just to calculate the amount.
		$mcc = MyCryptoCheckout();

		$order_total = $order->get_total();
		$currencies = $mcc->currencies();
		$currency = $currencies->get( $currency_id );
		$wallet = $mcc->wallets()->get_dustiest_wallet( $currency_id );

		$wallet->use_it();
		$mcc->wallets()->save();

		$woocommerce_currency = get_woocommerce_currency();
		$amount = $mcc->markup_amount( $order_total );
		$amount = $currency->convert( $woocommerce_currency, $amount );
		$amount = $currency->find_next_available_amount( $amount );

		$order->update_meta_data( '_mcc_amount', $amount );
		$order->update_meta_data( '_mcc_currency_id', $currency_id );
		$order->update_meta_data( '_mcc_confirmations', $wallet->confirmations );
		$order->update_meta_data( '_mcc_created_at', time() );

		// Get the gateway instance.
		$gateway = \WC_Gateway_MyCryptoCheckout::instance();
		$test_mode = $gateway->get_option( 'test_mode' );
		if ( $test_mode == 'yes' )
		{
			$mcc->debug( 'WooCommerce gateway is in test mode.' );
			$payment_id = 1;		// Nobody will ever have 1 again, so it's safe to use.
		}
		else
			$payment_id = 0;		// 0 = not sent.

		$order->update_meta_data( '_mcc_payment_id', $payment_id );
		$order->update_meta_data( '_mcc_to', $wallet->get_address() );

		// We want to keep the account locked, but still enable the is_available gateway check to work for the rest of this session.
		$this->__just_used = true;
	}

	/**
		@brief		Maybe send this order to the API.
		@since		2017-12-25 16:21:06
	**/
	public function woocommerce_checkout_update_order_meta( $order_id )
	{
		$order = wc_get_order( $order_id );
		if ( $order->get_payment_method() != static::$gateway_id )
			return;
		if ( $order->get_meta( '_mcc_payment_id' ) != 0 )
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
