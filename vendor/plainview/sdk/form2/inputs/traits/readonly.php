<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Readonly attribute manipulation.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20131015
**/
trait readonly
{
	/**
		@brief		Set the readonly attribute of this object.
		@param		bool		$readonly		The new state of the readonly attribute.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function readonly( $readonly = 'readonly' )
	{
		if ( $readonly == 'readonly' )
			$this->set_attribute( 'readonly', $readonly );
		else
			$this->clear_attribute( 'readonly' );
		return $this;
	}

	/**
		@brief		Return if the object is readonly.
		@return		bool		True if the readonly attribute is set.
		@since		20130524
	**/
	public function is_readonly()
	{
		return $this->get_attribute( 'readonly' ) == 'readonly';
	}
}
