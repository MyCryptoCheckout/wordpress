<?php

namespace mycryptocheckout\actions;

/**
	@brief		This wallet has now been used.
	@details	The wallet will be saved after this action.
	@since		2018-07-01 13:26:12
**/
class use_wallet
	extends action
{
	/**
		@brief		IN: The currency object of the wallet being used.
		@since		2018-07-01 15:12:50
	**/
	public $currency;

	/**
		@brief		IN/OUT: The wallet that is being used.
		@since		2018-07-01 15:13:17
	**/
	public $wallet;

	/**
		@brief		IN: The wallets collection.
		@since		2018-07-01 15:13:01
	**/
	public $wallets;
}
