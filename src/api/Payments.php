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
}
