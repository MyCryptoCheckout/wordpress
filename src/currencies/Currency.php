<?php

namespace mycryptocheckout\currencies;

use \Exception;

/**
	@brief		The base currency that is loaded into the Currencies collection.
	@since		2017-12-09 20:00:32
**/
abstract class Currency
{
	/**
		@brief		Convert this amount to this currency.
		@since		2017-12-10 20:05:14
	**/
	public function convert( $original_currency, $original_amount )
	{
		return rand( 1000, 10000000 );
	}

	/**
		@brief		Return the ID of this currency.
		@since		2017-12-09 20:05:36
	**/
	public function get_id()
	{
		$class = get_called_class();
		$class = preg_replace( '/.*\\\/', '', $class );
		return $class;
	}

	/**
		@brief		Return the name of this currency.
		@since		2017-12-09 20:05:36
	**/
	public abstract function get_name();

	/**
		@brief		Validate that this address looks normal.
		@since		2017-12-09 20:09:17
	**/
	public static function validate_address( $address )
	{
		return true;
	}

	/**
		@brief		Check that the address length is exactly x characters.
		@since		2017-12-09 20:21:55
	**/
	public static function validate_address_length( $address, $length )
	{
		if ( strlen( $address ) != $length )
			throw new Exception( sprintf(
				__( 'The address must be exactly %d characters long.', 'mycryptocheckout' ),
				$length
			) );
	}
}
