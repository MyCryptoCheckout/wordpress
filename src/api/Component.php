<?php

namespace mycryptocheckout\api;

/**
	@brief		Base component class.
	@since		2017-12-11 19:17:09
**/
class Component
{
	/**
		@brief		Construct.
		@since		2017-12-11 19:16:30
	**/
	public function __construct( $api )
	{
		$this->api = $api;
	}
}
