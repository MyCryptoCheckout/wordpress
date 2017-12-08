<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Text input with number specialization.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130820
**/
class number
	extends text
{
	use traits\max;
	use traits\min;
	use traits\size;
	use traits\step;

	public $type = 'number';

	public function _construct()
	{
		// Remove all non-numbers from the value.
		$this->add_value_filter( 'number' );
	}

	/**
		@brief		Returns only a number.
		@details

		Filters out all non-numeric characters and returns the number value of the rest.

		@param		string		$value		String value containing a number.
		@return		float		Number value of $value.
	**/
	public function value_filter_number( $value )
	{
		$value = preg_replace( "/[^0-9-\.]/", "", $value );
		// No value in the input? Then return false to signify that there wasn't anything there at all.
		if ( $value === '' )
			return false;
		return floatval( $value );
	}
}
