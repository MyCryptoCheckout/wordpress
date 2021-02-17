<?php

namespace mycryptocheckout\api\v2;

use mycryptocheckout\api\v2\Exception;

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
	public function add( Payment $payment )
	{
		$json = $this->api()->send_post_with_account( 'payment/add', [ 'payment' => $payment->to_array() ] );
		if ( ! property_exists( $json, 'payment_id' ) )
		{
			$this->api()->debug( $json );
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
		@brief		Complete a payment on the API.
		@since		2018-03-25 22:30:37
	**/
	/**
		@brief		Complete a payment on the API.
		@since		2019-04-22 11:50:49
	**/
	public function complete( $payment_id )
	{
		$json = $this->api()->send_post_with_account( 'payment/complete/' . $payment_id, [] );
		return true;
	}

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

		// Ensure that every payment is unique.
		$microtime = microtime( true );
		$microtime = preg_replace( '/.*\./', '', $microtime );		// We want only the microseconds.
		$payment->data()->set( 'microtime', $microtime );

		// Shoudl we extract old data?
		if ( $data !== null )
			foreach( (array) $data as $key => $value )
				$payment->$key = $value;

		return $payment;
	}
}
