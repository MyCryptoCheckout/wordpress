<?php

namespace mycryptocheckout\api\v2;

/**
	@brief		Base component class.
	@since		2018-10-08 19:17:14
**/
class Component
{
	/**
		@brief		Construct.
		@since		2018-10-08 19:17:14
	**/
	public function __construct( $api )
	{
		$this->__api = $api;
		$this->_construct();
	}

	/**
		@brief		Optional constructor for each component.
		@since		2018-10-08 19:45:07
	**/
	public function _construct()
	{
	}

	/**
		@brief		Return the API instance.
		@since		2018-10-08 20:03:42
	**/
	public function api()
	{
		return $this->__api;
	}
}
