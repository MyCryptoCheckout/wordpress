<?php

namespace mycryptocheckout\currencies\erc20;

/**
	@brief		ERC20
	@since		2018-03-11 12:51:00
**/
class STAKE
	extends ERC20
{
	/**
		@brief		Return the name of this currency.
		@since		2017-12-09 20:05:36
	**/
	public function get_name()
	{
		return 'StakeIt';
	}
}
