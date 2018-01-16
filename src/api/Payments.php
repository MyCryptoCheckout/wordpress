<?php

namespace mycryptocheckout\api;

use Exception;

/**
	@brief		Payment handling.
	@since		2017-12-21 23:28:34
**/
class Payments
	extends Component
{
	/**
		@brief		Send a new payment to the server.
		@since		2017-12-21 23:28:43
	**/
	public function add( $payment )
	{
		$json = $this->api->send_post_with_account( 'payment/add', [ 'payment' => $payment->to_array() ] );
		if ( ! property_exists( $json, 'payment_id' ) )
		{
			MyCryptoCheckout()->debug( $json );
			throw new Exception( 'No payment ID received from the server.' );
		}
		return $json->payment_id;
	}

	/**
		@brief		Generate a Payment class from an order.
		@since		2017-12-21 23:47:17
	**/
	public static function generate_payment_from_order( $post_id )
	{
		$payment = new Payment();
		$payment->amount = get_post_meta( $post_id,  '_mcc_amount', true );
		$payment->confirmations = get_post_meta( $post_id,  '_mcc_confirmations', true );
		$payment->created_at = get_post_meta( $post_id,  '_mcc_created_at', true );
		$payment->currency_id = get_post_meta( $post_id,  '_mcc_currency_id', true );
		$payment->to = get_post_meta( $post_id,  '_mcc_to', true );

		// If we are on a network, then note down the site data.
		if ( MULTISITE )
		{
			$payment->data()->set( 'site_id', get_current_blog_id() );
			$payment->data()->set( 'site_url', get_option( 'siteurl' ) );
		}

		return $payment;
	}

	/**
		@brief		Send a payment for a post ID.
		@since		2018-01-02 19:16:06
	**/
	public function send( $post_id )
	{
		$attempts = intval( get_post_meta( $post_id, '_mcc_attempts', true ) );

		MyCryptoCheckout()->api()->account()->lock()->save();

		try
		{
			$payment = static::generate_payment_from_order( $post_id );
			$payment_id = $this->add( $payment );
			update_post_meta( $post_id,  '_mcc_payment_id', $payment_id );
			MyCryptoCheckout()->debug( 'Payment for order %d has been added as payment #%d.', $post_id, $payment_id );
		}
		catch ( Exception $e )
		{
			$attempts++;
			update_post_meta( $post_id,  '_mcc_attempts', $attempts );
			MyCryptoCheckout()->debug( 'Failure #%d trying to send the payment for order %d. %s', $attempts, $post_id, $e->getMessage() );
			if ( $attempts > 48 )	// 48 hours, since this is usually run on the hourly cron.
			{
				// TODO: Give up and inform the admin of the failure.
				MyCryptoCheckout()->debug( 'We have now given up on trying to send the payment for order %d.', $post_id );
				update_post_meta( $post_id,  '_mcc_payment_id', -1 );
			}
		}
	}

	/**
		@brief		Send unsent payments.
		@since		2017-12-24 12:11:03
	**/
	public function send_unsent_payments()
	{
		// Find all posts in the database that do not have a payment ID.
		global $wpdb;
		$query = sprintf( "SELECT `post_id` FROM `%s` WHERE `meta_key` = '_mcc_payment_id' AND `meta_value` = '0'",
			$wpdb->postmeta
		);
		$results = $wpdb->get_col( $query );
		if ( count( $results ) < 1 )
			return;
		MyCryptoCheckout()->debug( 'Unsent payments: %s', implode( ', ', $results ) );
		foreach( $results as $post_id )
			$this->send( $post_id );
	}
}
