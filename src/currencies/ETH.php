<?php

namespace mycryptocheckout\currencies;

/**
	@brief		Etherium
	@details	It actually has 18 decimal places, but some wallets (Coinbase) will unnecessarily round such small amounts off, making payments impossible.
	@since		2017-12-09 20:01:50
**/
class ETH
	extends Currency
{
	/**
		@brief		Return the name of this currency.
		@since		2017-12-09 20:05:36
	**/
	public function get_name()
	{
		return 'Ethereum';
	}

	/**
		@brief		Return the length of the wallet address.
		@since		2017-12-24 10:58:43
	**/
	public static function get_address_length()
	{
		return 42;
	}
}
