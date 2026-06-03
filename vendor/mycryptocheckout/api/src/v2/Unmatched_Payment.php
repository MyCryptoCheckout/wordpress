<?php

namespace mycryptocheckout\api\v2;

/**
	@brief		A payment that was sent to one of the user's wallets, but was not handled by MCC>
	@details	This is usually due to the amount being incorrect.
	@since		2026-06-03 16:54:57
**/
class Unmatched_Payment
{
	/**
		@brief		The cryptocurrency amount to watch for as a string.
		@details	"4.223432" or "12" or "5000000"
					We use strings since floats are awfully unreliable.
		@since		2026-06-03 16:54:57
	**/
	public $amount;

	/**
		@brief		Unix time when this payment was detected.
		@since		2026-06-03 16:59:05
	**/
	public $created_at;

	/**
		@brief		The symbol / ID of the currency.
		@since		2026-06-03 16:59:05
	**/
	public $currency_id;

	/**
		@brief		The wallet address to which the payment was sent.
		@since		2026-06-03 16:59:05
	**/
	public $to;

	/**
		@brief		The ID of the transaction the payment was found in.
		@since		2026-06-03 16:59:05
	**/
	public $transaction_id;

	/**
		@brief		Constructor.
		@since		2026-06-03 17:19:05
	**/
	public function __construct( $raw )
	{
		foreach( [
			'amount',
			'created_at',
			'currency_id',
			'to',
			'transaction_id',
		] as $key )
			$this->$key = $raw->$key;
	}
}
