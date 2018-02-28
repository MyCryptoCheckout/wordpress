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

		$cryptocurrency_amount = rtrim( sprintf( '%.20F', $cryptocurrency_amount ), '0' );
		$cryptocurrency_amount = $this->trim_decimals( $cryptocurrency_amount );

		return $cryptocurrency_amount;
	}

	/**
		@brief		Find the next available payment amount for this currency.
		@since		2018-01-06 09:04:51
	**/
	public function find_next_available_amount( $amount )
	{
		$account = MyCryptoCheckout()->api()->account();
		$precision = $this->get_decimal_precision();

		// Keep incrementing the account until a "free" amount is found.
		while( ! $account->is_payment_amount_available( $this->get_id(), $amount ) )
			$amount = MyCryptoCheckout()->increase_floating_point_number( $amount, $precision );

		return $amount;
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
		@brief		Return the decimal precision of this currency.
		@since		2018-01-06 06:34:38
	**/
	public function get_decimal_precision()
	{
		// BTC is 8.
		return 8;
	}

	/**
		@brief		Return the group of the currency.
		@details	This is used mainly for ETH tokens.
		@since		2018-02-23 15:16:24
	**/
	public function get_group()
	{
		$g = new Group();
		$g->name = __( 'Main blockchains', 'mycryptocheckout' );
		$g->sort_order = 25;	// First!
		return $g;
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
		@brief		Does this currency support confirmations?
		@since		2018-01-05 13:46:45
	**/
	public function supports_confirmations()
	{
		return true;
	}

	/**
		@brief		Trim the decimals of this number.
		@since		2018-01-06 06:35:44
	**/
	public function trim_decimals( $amount )
	{
		$decimal = strpos( $amount, '.');
		if ( $decimal === false )
			return $amount;
		$amount = sprintf( '%s.%s',
			substr( $amount, 0, $decimal ),
			substr( $amount, $decimal + 1, $this->get_decimal_precision() )
		);
		return $amount;
	}

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
		if ( ! is_array( $length ) )
			$length = [ $length ];
		if ( ! in_array( strlen( $address ), $length ) )
			throw new Exception( sprintf(
				__( 'The address must be exactly %s characters long.', 'mycryptocheckout' ),
				implode( ' or ', $length )
			) );
	}
}
