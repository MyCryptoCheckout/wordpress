<?php

namespace mycryptocheckout\api;

/**
	@brief		General exception class, so the components don't have to keep \ escaping.
	@since		2017-12-11 19:52:59
**/
class Exception
	extends \Exception
{
	/**
		@brief		Name it properly.
		@since		2017-12-11 20:02:20
	**/
	public function get_message()
	{
		return $this->getMessage();
	}
}
