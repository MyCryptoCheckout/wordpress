<?php

namespace mycryptocheckout\actions;

/**
	@brief		Return the dustiest wallet for this currency.
	@details	This is called during checkout to find the "dustiest" = the one used first. Or least used, or whatever.
	@since		2018-01-05 16:11:46
**/
class get_dustiest_wallet
	extends action
{
	/**
		@brief		IN: The currency ID.
		@since		2018-01-05 16:21:51
	**/
	public $currency_id;

	/**
		@brief		IN: Collection of wallets sorted by dustiest first.
		@since		2018-01-05 16:19:22
	**/
	public $dustiest_wallets;

	/**
		@brief		OUT: The dustiest wallet of this currency.
		@since		2018-01-05 16:18:43
	**/
	public $wallet;

	/**
		@brief		IN: A collection of the enabled wallets for this currency.
		@since		2018-01-05 16:22:10
	**/
	public $wallets;
}
