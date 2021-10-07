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
		$this->add_action( 'mycryptocheckout_complete_payment' );
		$this->add_action( 'template_redirect' );
		//$this->add_action( 'before_woocommerce_pay' );
		$this->add_action( 'wcs_new_order_created' );
		$this->add_filter( 'wcs_renewal_order_meta' );
		$this->add_action( 'woocommerce_admin_order_data_after_order_details' );
		$this->add_action( 'woocommerce_checkout_create_order', 10, 2 );
		$this->add_action( 'woocommerce_checkout_update_order_meta' );
		$this->add_filter( 'woocommerce_currencies' );
		$this->add_filter( 'woocommerce_currency_symbol', 10, 2 );
		$this->add_filter( 'woocommerce_get_checkout_payment_url', 10, 2 );
		$this->add_action( 'woocommerce_order_status_cancelled' );
		$this->add_action( 'woocommerce_order_status_completed' );
		$this->add_filter( 'woocommerce_payment_gateways' );
		$this->add_action( 'woocommerce_review_order_before_payment' );
		$this->add_action( 'woocommerce_sections_general' );
	}

	/**
		@brief		If this is an MCC order, redirect to order received immediately.
		@since		2019-12-11 22:56:26
	**/
	public function before_woocommerce_pay()
	{
		// Extract the order ID.
		global $wp;
		$order_id = intval( $wp->query_vars['order-pay'] );

		// Order must be valid.
		$order = new \WC_Order( $order_id );
		if ( ! $order )
			return;

		// Is this an mcc transaction?
		$mcc_currency_id = $order->get_meta( '_mcc_currency_id' );
		if ( ! $mcc_currency_id )
			return;

		// And now redirect the buyer to the correct page.
		$url = $order->get_checkout_order_received_url();

		wp_redirect( $url );
		exit;
	}

	/**
		@brief		Check to see if WC has the correct amount of decimals set.
		@since		2018-06-14 12:43:58
	**/
	public function check_decimal_setting()
	{
		$wc_currency = get_woocommerce_currency();
		$currency = MyCryptoCheckout()->currencies()->get( $wc_currency );
		if ( ! $currency )
			return;
		// Get the WC decimal precision.
		$wc_decimals = get_option( 'woocommerce_price_num_decimals' );
		if ( $wc_decimals == $currency->decimal_precision )
			return;
		throw new Exception( sprintf( "Since you are using virtual currency %s as your WooCommerce currency, please change the decimal precision from %s to match MyCyyptoCheckout's: %s", $wc_currency, $wc_decimals, $currency->decimal_precision ) );
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

		// Do we know about this virtual currency?
		$wallet = MyCryptoCheckout()->wallets()->get_dustiest_wallet( $wc_currency );
		if ( ! $wallet )
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
		try
		{
			MyCryptoCheckout()->api()->payments()->send_unsent_payments();
		}
		catch( Exception $e )
		{
			$this->debug( $e->getMessage() );
		}
	}

	/**
		@brief		Payment was abanadoned.
		@since		2018-01-06 15:59:11
	**/
	public function mycryptocheckout_cancel_payment( $action )
	{
		$this->do_with_payment_action( $action, function( $action, $order_id )
		{
			if ( ! function_exists( 'WC' ) )
				return;

			$order = wc_get_order( $order_id );
			if ( ! $order )
				return;

			// Consider this action finished as soon as we find the order.
			$action->applied++;

			// Only cancel is the order is unpaid.
			if ( $order->get_status() != 'pending' )
				return MyCryptoCheckout()->debug( 'WC order %d on blog %d is not unpaid. Can not cancel.', $order_id, get_current_blog_id() );

			MyCryptoCheckout()->debug( 'Marking WC payment %s on blog %d as cancelled.', $order_id, get_current_blog_id() );
			$order->update_status( 'cancelled', 'Payment timed out.' );
			do_action( 'woocommerce_cancelled_order', $order->get_id() );
		} );
	}

	/**
		@brief		mycryptocheckout_complete_payment
		@since		2017-12-26 10:17:13
	**/
	public function mycryptocheckout_complete_payment( $payment )
	{
		$this->do_with_payment_action( $payment, function( $action, $order_id )
		{
			if ( ! function_exists( 'WC' ) )
				return;

			$order = wc_get_order( $order_id );
			if ( ! $order )
				return;

			// Consider this action finished as soon as we find the order.
			$action->applied++;

			$payment = $action->payment;

			MyCryptoCheckout()->debug( 'Marking WC payment %s on blog %d as paid.', $order_id, get_current_blog_id() );
			$order->payment_complete( $payment->transaction_id );

			// Since WC is not yet loaded properly, we have to load the gateway settings ourselves.
			$options = get_option( 'woocommerce_mycryptocheckout_settings', true );
			$options = maybe_unserialize( $options );
			if ( isset( $options[ 'payment_complete_status' ] ) )
				if ( $options[ 'payment_complete_status' ] != '' )
				{
					// The default is '', which means don't do anything.
					MyCryptoCheckout()->debug( 'Marking WC payment %s on blog %d as %s.',
						$order_id,
						get_current_blog_id(),
						$options[ 'payment_complete_status' ]
					);
					$order->set_status( $options[ 'payment_complete_status' ] );
					$order->save();
				}
		} );
	}

	/**
		@brief		Maybe redirect to the order recieved page for Waves transactions.
		@since		2019-07-27 19:43:13
	**/
	public function template_redirect()
	{
		if ( ! isset( $_GET[ 'txId' ] ) )	// This is what waves adds.
			return;
		if ( count( $_GET ) !== 1 )			// The waves payment API strips out every parameter.
			return;
		if ( ! is_order_received_page() )	// It at least returns the buyer to the order received page.
			return;

		// Extract the order ID.
		global $wp;
		$order_id = intval( $wp->query_vars['order-received'] );

		// Order must be valid.
		$order = new \WC_Order( $order_id );
		if ( ! $order )
			return;

		// Is this an mcc transaction?
		$mcc_currency_id = $order->get_meta( '_mcc_currency_id' );
		if ( ! $mcc_currency_id )
			return;

		// And now redirect the buyer to the correct page.
		$url = $order->get_checkout_order_received_url();

		wp_redirect( $url );
		exit;
	}

	/**
		@brief		Since sub orders are cloned, we need to remove the payment info.
		@since		2020-03-18 20:21:01
	**/
	public function wcs_new_order_created( $order )
	{
		MyCryptoCheckout()->debug( 'Deleting payment ID for subscription order %s', $order->get_id() );
		$order->delete_meta_data( '_mcc_payment_id' );
		$order->save();
		return $order;
	}

	/**
		@brief		Remove our MCC meta since WCS is nice enough to copy ALL meta from old, expired orders.
		@since		2020-03-20 15:40:02
	**/
	public function wcs_renewal_order_meta( $order_meta )
	{
		MyCryptoCheckout()->debug( 'Order meta %s', $order_meta );
		foreach( $order_meta as $index => $meta )
		{
			// Remove all MCC meta.
			if ( strpos( $meta[ 'meta_key' ], '_mcc_' ) === 0 )
				unset( $order_meta[ $index ] );
		}
		MyCryptoCheckout()->debug( 'Order meta %s', $order_meta );
		return $order_meta;
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
		}

		echo $r;
	}

	/**
		@brief		Cancel an order on the server.
		@since		2018-03-25 22:28:25
	**/
	public function woocommerce_order_status_cancelled( $order_id )
	{
		$order = wc_get_order( $order_id );
		$payment_id = $order->get_meta( '_mcc_payment_id' );
		if ( $payment_id < 2 )		// 1 is for test mode.
			return;
		MyCryptoCheckout()->debug( 'Cancelling payment %d for order %s', $payment_id, $order_id );
		MyCryptoCheckout()->api()->payments()->cancel( $payment_id );
	}

	/**
		@brief		Complete an order on the server.
		@since		2019-04-22 11:50:06
	**/
	public function woocommerce_order_status_completed( $order_id )
	{
		$order = wc_get_order( $order_id );
		$payment_id = $order->get_meta( '_mcc_payment_id' );
		if ( $payment_id < 2 )		// 1 is for test mode.
			return;
		MyCryptoCheckout()->debug( 'Completing payment %d for order %s', $payment_id, $order_id );
		MyCryptoCheckout()->api()->payments()->complete( $payment_id );
	}

	/**
		@brief		Add the meta fields.
		@since		2017-12-10 21:35:29
	**/
	public function woocommerce_checkout_create_order( $order, $data )
	{
		if ( $order->get_payment_method() != static::$gateway_id )
			return;

		$account = MyCryptoCheckout()->api()->account();
		$available_for_payment = $account->is_available_for_payment();

		MyCryptoCheckout()->debug( 'Creating order! Available: %d', $available_for_payment );

		$currency_id = sanitize_text_field( $_POST[ 'mcc_currency_id' ] );

		// Get the gateway instance.
		$gateway = \WC_Gateway_MyCryptoCheckout::instance();

		// All of the below is just to calculate the amount.
		$mcc = MyCryptoCheckout();

		$order_total = $order->get_total();
		$currencies = $mcc->currencies();
		$currency = $currencies->get( $currency_id );
		$wallet = $mcc->wallets()->get_dustiest_wallet( $currency_id );
		$address = $wallet->get_address();
		$wallet->use_it();
		$mcc->wallets()->save();

		$woocommerce_currency = get_woocommerce_currency();
		$amount = $mcc->markup_amount( [
			'amount' => $order_total,
			'currency_id' => $currency_id,
		] );
		MyCryptoCheckout()->debug( 'Marking up total: %s %s -> %s', $order_total, $woocommerce_currency, $amount );
		$amount = $currency->convert( $woocommerce_currency, $amount );
		if ( $amount == 0 )
		{

			$account = MyCryptoCheckout()->api()->account();
			MyCryptoCheckout()->debug( 'Error with conversion! %s', $account );
		}
		else
			MyCryptoCheckout()->debug( 'Conversion: %s', $amount );
		$next_amount = $amount;
		$precision = $currency->get_decimal_precision();

		$next_amount = $currency->find_next_available_amount( $next_amount );
		$next_amounts = [ $next_amount ];

		// Increase the next amount.
		$spread = intval( $gateway->get_option( 'payment_amount_spread' ) );
		for( $counter = 0; $counter < $spread ; $counter++ )
		{
			// Help find_next_available_amount by increasing the value by 1.
			$next_amount = MyCryptoCheckout()->increase_floating_point_number( $next_amount, $precision );
			// And now find the next amount.
			$next_amount = $currency->find_next_available_amount( $next_amount );
			$next_amounts []= $next_amount;
		}

		MyCryptoCheckout()->debug( 'Next amounts: %s', $next_amounts );

		// Select a next amount at random.
		$amount = $next_amounts[ array_rand( $next_amounts ) ];

		MyCryptoCheckout()->debug( 'Amount selected: %s', $amount );

		// Are we paying in the same currency as the native currency?
		if ( $currency_id == get_woocommerce_currency() )
		{
			// Make sure the order total matches our expected amount.
			$order->set_total( $amount );
			$order->save();
		}

		$payment = MyCryptoCheckout()->api()->payments()->create_new();
		$payment->amount = $amount;
		$payment->currency_id = $currency_id;

		$test_mode = $gateway->get_option( 'test_mode' );
		if ( $test_mode == 'yes' )
		{
			$mcc->debug( 'WooCommerce gateway is in test mode.' );
			$payment_id = 1;		// Nobody will ever have 1 again, so it's safe to use.
		}
		else
			$payment_id = 0;		// 0 = not sent.
		$order->update_meta_data( '_mcc_payment_id', $payment_id );

		// Save the non-default payment timeout hours.
		$payment->timeout_hours = intval( $gateway->get_option( 'payment_timeout_hours' ) );

		$wallet->apply_to_payment( $payment );
		MyCryptoCheckout()->autosettlements()->apply_to_payment( $payment );

		MyCryptoCheckout()->debug( 'Payment as created: %s', $payment );

		// This stuff should be handled by the Payment object, but the order doesn't exist yet...
		$order->update_meta_data( '_mcc_amount', $payment->amount );
		$order->update_meta_data( '_mcc_confirmations', $payment->confirmations );
		$order->update_meta_data( '_mcc_created_at', $payment->created_at );
		$order->update_meta_data( '_mcc_currency_id', $payment->currency_id );
		$order->update_meta_data( '_mcc_payment_timeout_hours', $payment->timeout_hours );
		$order->update_meta_data( '_mcc_to', $payment->to );
		$order->update_meta_data( '_mcc_payment_data', $payment->data );

		$action = MyCryptoCheckout()->new_action( 'woocommerce_create_order' );
		$action->order = $order;
		$action->payment = $payment;
		$action->execute();

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
		do_action( 'mycryptocheckout_woocommerce_order_created', $order );

		$gateway = \WC_Gateway_MyCryptoCheckout::instance();
		$send_new_order_invoice = $gateway->get_option( 'send_new_order_invoice' );
		if ( $send_new_order_invoice != 'no' )
			WC()->mailer()->customer_invoice( $order );
	}

	/**
		@brief		woocommerce_currencies
		@since		2021-05-02 10:54:15
	**/
	public function woocommerce_currencies( $currencies )
	{
		$wallets = mycryptocheckout()->wallets();
		$mcc_currencies = mycryptocheckout()->currencies();
		foreach( $wallets as $wallet )
		{
			if ( ! $wallet )
				continue;
			$currency_id = $wallet->get_currency_id();
			$currency = $mcc_currencies->get( $currency_id );
			if ( ! $currency )
				continue;
			$name = $currency->get_name();
			$currencies[ $currency_id ] = $name;
		}
		return $currencies;
	}

	/**
		@brief		woocommerce_currency_symbol
		@since		2021-05-02 11:00:05
	**/
	public function woocommerce_currency_symbol( $currency_symbol, $currency )
	{
		$mcc_currencies = mycryptocheckout()->currencies();
		if ( ! $mcc_currencies->has( $currency ) )
			return $currency_symbol;
		return $currency;
	}

	/**
		@brief		woocommerce_get_checkout_payment_url
		@since		2018-06-12 21:05:04
	**/
	public function woocommerce_get_checkout_payment_url( $url, $order )
	{
		// We only override the payment URL for orders that are handled by us.
		if ( $order->get_meta( '_mcc_payment_id' ) < 1 )
			return $url;
		return $order->get_checkout_order_received_url();
	}

	/**
		@brief		woocommerce_sections_general
		@since		2018-06-14 15:10:12
	**/
	public function woocommerce_sections_general()
	{
		try
		{
			MyCryptoCheckout()->woocommerce->check_decimal_setting();
		}
		catch ( Exception $e )
		{
			echo MyCryptoCheckout()->error_message_box()->text( $e->getMessage() );
		}
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

	/**
		@brief		Apply a width fix for some themes. Otherwise the width (incl amount) gets way too long.
		@since		2018-03-12 19:09:01
	**/
	public function woocommerce_review_order_before_payment()
	{
		echo '<style>.wc_payment_method #mcc_currency_id_field select#mcc_currency_id { width: 100%; }</style>';
	}
}
