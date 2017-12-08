<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Text input with date / time formatting.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class datetime
	extends date
{
	public $type = 'datetime';

	public function _construct()
	{
		$this->add_value_filter( 'datetime' );
	}

	/**
		@brief		Returns a valid date time.
		@param		string		$value		String value containing a date and time.
		@return		string		Date and time.
	**/
	public function value_filter_datetime( $value )
	{
		$date = strtotime( $value );
		return date( 'Y-m-d H:i:s', $date );
	}
}
