<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Placeholder attribute manipulation.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait placeholder
{
	/**
		@brief		Return the current placeholder attribute.
		@return		string		The current placeholder attribute.
		@see		set_placeholder()
		@since		20130524
	**/
	public function get_placeholder()
	{
		return $this->get_attribute( 'placeholder' );
	}

	/**
		@brief		Sprintf and set the placeholder attribute.
		@details	The first parameter is the string to sprintf. Add additional parameters as necessary.

		The placeholder text is filtered.

		@param		string		$placeholder		The new placeholder text.
		@return		this		Object chaining.
		@see		get_placeholder()
		@see		set_placeholder()
		@since		20130524
	**/
	public function placeholder( $placeholder )
	{
		$result = @call_user_func_array( 'sprintf' , func_get_args() );
		if ( $result == '' )
			$result = $placeholder;
		return $this->set_placeholder( $result );
	}

	/**
		@brief		Translate and set the placeholder attribute.
		@deprecated	Since 20180207
		@details	The placeholder text is filtered.
		@param		string		$placeholder		The new placeholder text.
		@return		this		Object chaining.
		@see		get_placeholder()
		@see		set_placeholder()
		@since		20130524
	**/
	public function placeholder_( $placeholder )
	{
		$placeholder = call_user_func_array( array( $this->container, '_' ), func_get_args() );
		return $this->set_placeholder( $placeholder );
	}

	/**
		@brief		Filter and set the placeholder text.
		@param		string		$placeholder		The new placeholder text.
		@return		this		Object chaining.
		@see		get_placeholder()
		@since		20130524
	**/
	public function set_placeholder( $placeholder )
	{
		$placeholder = \plainview\sdk_mcc\form2\form::filter_text( $placeholder );
		return $this->set_attribute( 'placeholder', $placeholder );
	}

}
