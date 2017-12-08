<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Manipulate the optimum attribute.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait optimum
{
	/**
		@brief		Sets the input's optimum value.
		@param		int			$optimum		The new optimum value for the input.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function optimum( $optimum )
	{
		return $this->set_attribute( 'optimum', $optimum );
	}
}

