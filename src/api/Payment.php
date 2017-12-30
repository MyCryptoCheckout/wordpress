<?php

namespace mycryptocheckout\api;

/**
	@brief		A payment to be sent to the server for monitoring.
	@since		2017-12-21 23:35:59
**/
class Payment
{
	/**
		@brief		The amount to watch for as a string.
		@since		2017-12-21 23:36:58
	**/
	public $amount;

	/**
		@brief		How many confirmations to require to be regarded as paid.
		@since		2017-12-24 12:14:06
	**/
	public $confirmations;

	/**
		@brief		When this payment was created.
		@since		2017-12-24 11:46:54
	**/
	public $created_at;

	/**
		@brief		The ID of the currency we are expecting the payment in.
		@since		2017-12-21 23:36:56
	**/
	public $currency_id;

	/**
		@brief		From which address?
		@since		2017-12-21 23:36:42
	**/
	public $from;

	/**
		@brief		The address to which we are expecting payment.
		@since		2017-12-21 23:36:54
	**/
	public $to;

	/**
		@brief		Return this object as an array.
		@since		2017-12-21 23:37:48
	**/
	public function to_array()
	{
		return [
			'amount' => $this->amount,
			'confirmations' => $this->confirmations,
			'created_at' => $this->created_at,
			'currency_id' => $this->currency_id,
			'from' => $this->from,
			'to' => $this->to,
		];
	}
}
