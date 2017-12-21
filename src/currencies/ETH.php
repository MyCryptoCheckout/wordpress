<?php

namespace mycryptocheckout\currencies;

/**
	@brief		Etherium
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
		@brief		Validate that this address looks normal.
		@since		2017-12-09 20:09:17
	**/
	public static function validate_address( $address )
	{
		static::validate_address_length( $address, 42 );
		return true;
	}
}
