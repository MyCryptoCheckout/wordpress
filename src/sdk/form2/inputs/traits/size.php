<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Size attribute manipulation.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait size
{
	/**
		@brief		Sets the input's size (and optionally maxlength) attribute.
		@param		int		$size		The new input size.
		@param		int		$maxlength	The input's new maximum length.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function size( $size, $maxlength = null )
	{
		if ( $maxlength !== null )
			$this->maxlength( max( $size, $maxlength ) );
		$size = intval( $size );
		return $this->set_attribute( 'size', $size );
	}
}

