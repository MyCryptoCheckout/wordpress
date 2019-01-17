<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Text input with date formatting.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class date
	extends number
{
	public $type = 'date';

	public function _construct()
	{
		// Remove all non-numbers from the value.
		$this->add_value_filter( 'date' );
	}

	/**
		@brief		Returns a valid date.
		@param		string		$value		String value containing a date.
		@return		float		Date in Y-m-d.
	**/
	public function value_filter_date( $value )
	{
		$date = strtotime( $value );
		return date( 'Y-m-d', $date );
	}
}
