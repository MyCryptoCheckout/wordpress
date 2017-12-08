<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Manipulate the high attribute.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait high
{
	/**
		@brief		Sets the input's high attribute.
		@param		int			$high		The input's new high attribute.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function high( $high )
	{
		return $this->set_attribute( 'high', $high );
	}
}

