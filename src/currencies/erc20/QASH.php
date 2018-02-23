<?php

namespace mycryptocheckout\currencies\erc20;

/**
	@brief		ERC20: Qash
	@since		2018-02-23 23:34:00
**/
class QASH
	extends ERC20
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
		return 'Qash';
	}
}
