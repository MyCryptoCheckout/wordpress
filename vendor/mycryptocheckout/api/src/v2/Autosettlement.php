<?php

namespace mycryptocheckout\api\v2;

/**
	@brief		An autosettlement is a bunch of API settings for autosettlement services.
	@since		2019-03-06 23:38:13
**/
class Autosettlement
{
	/**
		@brief		The name / type of the autosettlement.
		@since		2019-03-06 23:38:38
	**/
	public $type = '';

	/**
		@brief		Return the type of autosettlement.
		@since		2019-03-06 23:39:07
	**/
	public function get_type()
	{
		return $this->type;
	}

	/**
		@brief		Set the type of autosettlement.
		@since		2019-03-06 23:38:51
	**/
	public function set_type( $type )
	{
		$this->type = $type;
		return $this;
	}
}
