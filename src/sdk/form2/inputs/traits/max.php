<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Manipulate the max attribute.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait max
{
	/**
		@brief		Sets the input's maximum (and optionally minimum) value.
		@param		int			$max		The new maximum value.
		@param		int			$min		Optional new minimum value.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function max( $max, $min = null )
	{
		if ( $min !== null )
			$this->min( $min );
		$this->add_validation_method( 'max' );
		return $this->set_attribute( 'max', floatval( $max ) );
	}

	public function validate_max()
	{
		$value = floatval( $this->validation_value );
		$max = $this->get_attribute( 'max' );
		if ( $value > $max )
			$this->validation_error()->unfiltered_label( 'The value in %s (%s) is larger than the allowed maximum of %s.', '<em>' . $this->get_label()->content . '</em>', $value, $max );
	}
}
