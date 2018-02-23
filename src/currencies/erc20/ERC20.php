<?php

namespace mycryptocheckout\currencies\erc20;

/**
	@brief		Base class for all ERC20 tokens.
	@since		2018-02-23 15:15:46
**/
class ERC20
	extends \mycryptocheckout\currencies\ETH
{
	/**
		@brief		Return the group of the currency.
		@details	This is used mainly for ETH tokens.
		@since		2018-02-23 15:16:24
	**/
	public function get_group()
	{
		$g = new \mycryptocheckout\currencies\Group();
		$g->name = __( 'ERC20 tokens', 'mycryptocheckout' );
		return $g;
	}
}
