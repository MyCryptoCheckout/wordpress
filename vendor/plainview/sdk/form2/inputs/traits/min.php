<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Manipulate the minimum attribute.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait min
{
	/**
		@brief		Sets the input's minimum (and optionally maximum) value.
		@param		int			$min		The new minimum attribute.
		@param		int			$max		Optionally the new maximum value.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function min( $min, $max = null )
	{
		$this->add_validation_method( 'min' );
		if ( $max !== null )
			$this->max( $max );
		return $this->set_attribute( 'min', floatval( $min ) );
	}

	public function validate_min()
	{
		$value = floatval( $this->validation_value );
		$min = $this->get_attribute( 'min' );
		if ( $value < $min )
			$this->validation_error()->unfiltered_label( 'The value in %s (%s) is smaller than the allowed minimum of %s.', '<em>' . $this->get_label()->content . '</em>', $value, $min );
	}
}

