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
	public function convert( $currency, $amount )
	{
		// The exchange rates are stored in the account.
		$account = MyCryptoCheckout()->api()->account();
		// Convert to USD.
		if ( $currency != 'USD' )
		{
			$usd = $amount / $account->get_physical_exchange_rate( $currency );
			$usd = round( $usd, 2 );
		}
		else
			$usd = $amount;

		$cryptocurrency_amount = $account->get_virtual_exchange_rate( $this->get_id() );
		$cryptocurrency_amount = $usd * $cryptocurrency_amount;

		return $cryptocurrency_amount;
	}

	/**
		@brief		Return the length of the wallet address.
		@since		2017-12-24 10:58:43
	**/
	public static function get_address_length()
	{
		return 34;	// This is the default for a lot of coins.
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
		static::validate_address_length( $address, static::get_address_length() );
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
