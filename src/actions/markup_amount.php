<?php

namespace mycryptocheckout\actions;

/**
	@brief		Modify the amount / price of an item with these markup percent and fixed values.
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
