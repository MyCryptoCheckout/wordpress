<?php

namespace plainview\sdk_mcc\wordpress\row_actions;

class Main
	extends Action
{
	/**
		@brief		We wrap ourselves in a div.
		@since		2015-12-22 14:35:54
	**/
	public function __toString()
	{
		return sprintf( '<div><strong>%s</strong></div>', parent::__toString() );
	}
}
