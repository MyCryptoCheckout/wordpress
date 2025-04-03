<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Allow for date values.
	@since		2025-03-23 21:56:03
**/
trait max_date
{
	/**
		@brief		Sets the input's maximum (and optionally minimum) value.
		@param		int			$max		The new minimum attribute.
		@param		int			$min		Optionally the new maximum value.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function max( $max, $min = null )
	{
		$this->add_validation_method( 'max' );
		if ( $min !== null )
			$this->min( $min );
		return $this->set_attribute( 'max', $max );
	}

	public function validate_max()
	{
		$value = strtotime( $this->validation_value );
		$max = $this->get_attribute( 'max' );
		if ( $value > strtotime( $max ) )
			$this->validation_error()->unfiltered_label( 'The value in %s (%s) is larger than the allowed maximum of %s.', '<em>' . $this->get_label()->content . '</em>', $value, $max );
	}
}

