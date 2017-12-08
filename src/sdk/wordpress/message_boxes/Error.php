<?php

namespace plainview\sdk_mcc\wordpress\message_boxes;

/**
	@brief		An error type of message box.
	@since		2015-12-21 20:30:20
**/
class Error
	extends Box
{
	/**
		@brief		Error CSS class.
		@since		2015-12-21 20:30:56
	**/
	public function get_css_class()
	{
		return 'error notice is-dismissable';
	}
}
