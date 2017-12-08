<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Manipulate the autocomplete attribute.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait autocomplete
{
	/**
		@brief		Sets the autocomplete attribute.
		@details	The attribute can be one of: on, off or default.
		@param		string		$autocomplete		The new autocomplete value.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function autocomplete( $autocomplete = 'on' )
	{
		switch( $autocomplete )
		{
			case 'on':
			case 'off':
			case 'default':
				// No change
				break;
			default:
				// Anything else is on. See http://www.w3.org/community/webed/wiki/HTML/Elements/input/text for default value.
				$autocomplete = 'on';
		}
		return $this->set_attribute( 'autocomplete', $autocomplete );
	}
}

