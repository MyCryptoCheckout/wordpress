<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Manipulate the disabled attribute.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20131015
**/
trait disabled
{
	/**
		@brief		Set the disabled attribute of this option.
		@param		bool		$disabled		The new state of the disabled attribute.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function disabled( $disabled = 'disabled' )
	{
		if ( $disabled == 'disabled' )
			$this->set_attribute( 'disabled', $disabled );
		else
			$this->clear_attribute( 'disabled' );
		return $this;
	}

	/**
		@brief		Query the status of the disabled attribute.
		@return		bool		True, if the object has the disabled attribute.
		@since		20130524
	**/
	public function is_disabled()
	{
		return $this->get_attribute( 'disabled' ) == 'disabled';
	}
}
