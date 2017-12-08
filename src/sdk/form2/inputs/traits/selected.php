<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Selected attribute trait for options.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait selected
{
	/**
		@brief		Set the selected attribute of this element.
		@param		bool		$selected		New selected attribute.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function selected( $selected = true )
	{
		if ( $selected )
			$this->set_attribute( 'selected', 'selected' );
		else
			$this->clear_attribute( 'selected' );
		return $this;
	}
}
