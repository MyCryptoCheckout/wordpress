<?php

namespace mycryptocheckout\actions;

/**
	@brief		Return the dustiest wallet for this currency.
	@details	This is called during checkout to find the "dustiest" = the one used first. Or least used, or whatever.
	@since		2018-01-05 16:11:46
**/
class markup_amount
	extends action
{
	/**
		@brief		IN: The fixed amount to mark up.
		@since		2018-01-05 16:30:37
	**/
	public $markup_amount;

	/**
		@brief		IN: The percent to mark up.
		@since		2018-01-05 16:30:37
	**/
	public $markup_percent;

	/**
		@brief		OUT: The marked up amount.
		@since		2018-01-05 16:30:37
	**/
	public $marked_up_amount;

	/**
		@brief		IN: The original amount before markup.
		@since		2018-01-05 16:30:37
	**/
	public $original_amount;
}
