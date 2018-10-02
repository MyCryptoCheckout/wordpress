<?php

namespace mycryptocheckout\currencies;

use Exception;

/**
	@brief		The base currency that is loaded into the Currencies collection.
	@since		2017-12-09 20:00:32
**/
class Currency
{
	use btc_hd_public_key_trait;
	/**
		@brief		Convert this amount to this currency.
		@since		2017-12-10 20:05:14
	**/
	public function convert( $currency, $amount )
	{
		// The exchange rates are stored in the account.
		$account = MyCryptoCheckout()->api()->account();

		// Do not convert if we are trying to convert from BTC to BTC.
		if( $currency == $this->get_id() )
			return $amount;

		$amount = static::normalize_amount( $amount );

		// Convert to USD.
		if ( $currency != 'USD' )
		{
			$exchange_rate = $account->get_physical_exchange_rate( $currency );
			if ( $exchange_rate == 0 )
			{
				$exchange_rate = $account->get_virtual_exchange_rate( $currency );
				if ( $exchange_rate == 0 )
					return PHP_INT_MAX;
			}
			$usd = $amount / $exchange_rate;
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
	public function get_address_length()
	{
		if ( isset( $this->address_length ) )
			return $this->address_length;
		return 34;	// This is the default for a lot of coins.
	}

	/**
		@brief		Return the decimal precision of this currency.
		@since		2018-01-06 06:34:38
	**/
	public function get_decimal_precision()
	{
		if ( isset( $this->decimal_precision ) )
			return $this->decimal_precision;
		// 8 is very common.
		return 8;
	}

	/**
		@brief		Return the group of the currency.
		@details	This is used mainly for ETH tokens.
		@since		2018-02-23 15:16:24
	**/
	public function get_group()
	{
		if ( isset( $this->group ) )
			return $this->group;
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
		if ( isset( $this->id ) )
			return $this->id;
		$class = get_called_class();
		$class = preg_replace( '/.*\\\/', '', $class );
		return $class;
	}

	/**
		@brief		Return the name of this currency.
		@details	You'll want to override this in your own currency.
		@since		2017-12-09 20:05:36
	**/
	public function get_name()
	{
		if ( isset( $this->name ) )
			return $this->name;
		return $this->get_id();
	}

	/**
		@brief		Normalize the amount into something that PHP likes to work with.
		@since		2018-07-04 15:27:07
	**/
	public static function normalize_amount( $amount )
	{
		$comma = strpos( $amount, ',');
		$point = strpos( $amount, '.');

		// Is a comma used?
		if ( $comma !== false )
		{
			// Is a point also used?
			if ( $point !== false )
			{
				// The comma is a thousands sep. Remove it.
				$amount = str_replace( ',', '', $amount );
			}
			else
			{
				// No point.
				// Here we are assuming that a ,00 is actually a point in most currencies.
				if( strrpos( $amount, ',' ) == strlen( $amount ) - 3 )
				{
					$amount = str_replace( ',', '.', $amount );
				}
				$amount = str_replace( ',', '', $amount );
			}
		}
		return $amount;
	}

	/**
		@brief		Set the decimal precision of the currency.
		@since		2018-03-11 22:10:26
	**/
	public function set_address_length( $address_length )
	{
		$this->address_length = $address_length;
		return $this;
	}

	/**
		@brief		Set the decimal precision of the currency.
		@since		2018-03-11 22:10:26
	**/
	public function set_decimal_precision( $decimal_precision )
	{
		$this->decimal_precision = $decimal_precision;
		return $this;
	}

	/**
		@brief		Set the group of the currency.
		@since		2018-03-11 22:10:26
	**/
	public function set_group( $group )
	{
		$this->group = $group;
		return $this;
	}

	/**
		@brief		Override the ID of this class.
		@since		2018-03-11 22:03:41
	**/
	public function set_id( $id )
	{
		$this->id = $id;
		return $this;
	}

	/**
		@brief		Set the name of the currency.
		@since		2018-03-11 22:10:26
	**/
	public function set_name( $name )
	{
		$this->name = $name;
		return $this;
	}

	/**
		@brief		Does this currency support a specific feature?
		@since		2018-06-30 18:30:33
	**/
	public function supports( $feature )
	{
		return isset( $this->supports->$feature );
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
		if ( strrpos( $amount, '.' ) == strlen( $amount ) - 1 )
			$amount = rtrim( $amount, '.' );
		return $amount;
	}

	/**
		@brief		Validate that this address looks normal.
		@since		2017-12-09 20:09:17
	**/
	public function validate_address( $address )
	{
		$this->validate_address_length( $address, $this->get_address_length() );
		return true;
	}

	/**
		@brief		Check that the address length is exactly x characters.
		@since		2017-12-09 20:21:55
	**/
	public function validate_address_length( $address, $length )
	{
		if ( ! is_array( $length ) )
			$length = [ $length ];
		if ( ! in_array( strlen( $address ), $length ) )
			throw new Exception( sprintf(
				__( 'The address must be exactly %s characters long.', 'mycryptocheckout' ),
				implode( ' or ', $length )
			) );
	}

	/**
		@brief		Receive the use_wallet action to modify the wallet if necessary.
		@since		2018-07-01 14:37:48
	**/
	public function use_wallet( $action )
	{
		if ( $this->supports( 'btc_hd_public_key' ) )
			$this->btc_hd_public_key_use_wallet( $action );
	}
}
