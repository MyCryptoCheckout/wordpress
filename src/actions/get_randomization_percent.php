<?php

namespace mycryptocheckout\actions;

/**
	@brief		Return the randomization percent used for cryptocurrency amounts.
	@since		2018-01-05 21:36:23
**/
class get_randomization_percent
	extends action
{
	/**
		@brief		IN / OUT: The percentage used for cryptocurrency randomization.
		@since		2018-01-05 21:36:51
	**/
	public $randomization_percent;
}
