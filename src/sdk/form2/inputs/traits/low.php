<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Manipulate the low attribute.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait low
{
	/**
		@brief		Sets the input's low value.
		@param		int			$low		The input's new low value.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function low( $low )
	{
		return $this->set_attribute( 'low', $low );
	}
}
