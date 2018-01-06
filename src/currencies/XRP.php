<?php

namespace mycryptocheckout\currencies;

/**
	@brief		Ripple
	@since		2018-01-05 12:52:22
**/
class XRP
	extends Currency
{
	/**
		@brief		Return the decimal precision of this currency.
		@since		2018-01-06 06:34:38
	**/
	public function get_decimal_precision()
	{
		return 6;
	}

	/**
		@brief		Return the name of this currency.
		@since		2017-12-09 20:05:36
	**/
	public function get_name()
	{
		return 'Ripple';
	}

	/**
		@brief		Return the length of the wallet address.
		@since		2017-12-24 10:58:43
	**/
	public static function get_address_length()
	{
		// rf1BiGeXwwQoi8Z2ueFYTEXSwuJYfV2Jpn
		return 34;
	}

	/**
		@brief		Does this currency support confirmations?
		@since		2018-01-05 13:46:45
	**/
	public function supports_confirmations()
	{
		return false;
	}
}
