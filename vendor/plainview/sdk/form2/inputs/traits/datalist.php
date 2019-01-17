<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Assign a datalist to an input.
	@details

	Note that datalist() must be used instead of the expected list().

	This is because list() is a very reserved PHP word. Why doesn't HTML use datalist=?

	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait datalist
{
	/**
		@brief		Sets the input's list attribute.
		@param		string		$list		The name of the data list for this input.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function datalist( $list )
	{
		return $this->set_attribute( 'list', $list );
	}
}

