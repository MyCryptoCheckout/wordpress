<?php

namespace mycryptocheckout\api\v2;

/**
	@brief		Handles payments to the server.
	@since		2018-10-08 20:38:27
**/
abstract class Payments
	extends Component
{
	/**
		@brief		Send a new payment to the server.
		@since		2017-12-21 23:28:43
	**/
	public function add( $payment )
	{
		$json = $this->api()->send_post_with_account( 'payment/add', [ 'payment' => $payment->to_array() ] );
		if ( ! property_exists( $json, 'payment_id' ) )
		{
			MyCryptoCheckout()->debug( $json );
			throw new Exception( 'No payment ID received from the server.' );
		}
		return $json->payment_id;
	}

	/**
		@brief		Cancel a payment on the API.
		@since		2018-03-25 22:30:37
	**/
	public function cancel( $payment_id )
	{
		$json = $this->api()->send_post_with_account( 'payment/cancel/' . $payment_id, [] );
		return true;
	}

	/**
		@brief		Cancel a local payment.
		@since		2018-10-13 11:53:18
	**/
	public abstract function cancel_local( Payment $payment );

	/**
		@brief		Complete a local payment.
		@since		2018-10-13 11:53:39
	**/
	public abstract function complete_local( Payment $payment );

	/**
		@brief		Convenience method to create a new payment.
		@param		$data	object/array	Data to insert into the payment.
		@return		Payment
		@since		2018-09-20 20:58:53
	**/
	public static function create_new( $data = null )
	{
		$payment = new Payment();
		$payment->created_at = time();

		// Shoudl we extract old data?
		if ( $data !== null )
			foreach( (array) $data as $key => $value )
				$payment->$key = $value;

		return $payment;
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
