<?php

namespace mycryptocheckout\currencies;

/**
	@brief		Bitcoin cash.
	@since		2017-12-21 20:15:21
**/
class BCH
	extends BTC
{
	/**
		@brief		Return the name of this currency.
		@since		2017-12-09 20:05:36
	**/
	public function get_name()
	{
		return 'Bitcoin Cash';
	}

	/**
		@brief		Return the length of the wallet address.
		@since		2017-12-24 10:58:43
	**/
	public static function get_address_length()
	{
		return 34;	// This is the default for a lot of coins.
	}
}
