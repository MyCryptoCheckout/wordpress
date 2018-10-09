<?php

namespace mycryptocheckout\api\v2;

/**
	@brief		General exception class, so the components don't have to keep \ escaping.
	@since		2018-10-08 19:19:13
**/
class Exception
	extends \Exception
{
	/**
		@brief		Convenience method for returning the message.
		@since		2018-10-08 19:19:13
	**/
	public function get_message()
	{
		return $this->getMessage();
	}
}
