<?php

namespace mycryptocheckout\currencies;

/**
	@brief		Bitcoin.
	@since		2017-12-09 20:01:50
**/
class BTC
	extends Currency
{
	/**
		@brief		Return the name of this currency.
		@since		2017-12-09 20:05:36
	**/
	public function get_name()
	{
		return 'Bitcoin';
	}
}
