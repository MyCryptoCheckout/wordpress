<?php

namespace mycryptocheckout\actions;

/**
	@brief		Base action class.
	@since		2018-01-05 16:11:46
**/
class action
	extends \plainview\sdk_mcc\wordpress\actions\action
{
	/**
		@brief		Prefix.
		@since		2018-01-05 16:13:43
	**/
	public function get_prefix()
	{
		return 'mycryptocheckout_';
	}
}
