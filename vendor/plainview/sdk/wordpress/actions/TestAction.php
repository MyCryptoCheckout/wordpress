<?php

namespace plainview\sdk_mcc\wordpress\actions;

/**
	@brief		Used for testing the action class.
	@since		2021-02-05 16:38:26
**/
class TestAction
	extends action
{
	/**
		@brief		Get action prefix.
		@details	Optional prefix for all actions using this class. Suggest ending the prefix with an underscore.
		@since		2014-04-27 13:56:10
	**/
	public function get_prefix()
	{
		return 'abc';
	}

	/**
		@brief		Get action prefix.
		@details	Optional prefix for all actions using this class. Suggest ending the prefix with an underscore.
		@since		2014-04-27 13:56:10
	**/
	public function get_suffix()
	{
		return 'xyz';
	}
}