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
}
