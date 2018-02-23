<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Manipulate the minlength validation attribute.
	@details	This is an non-standard input attribute that exists solely for validation.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait minlength
{
	/**
		@brief		Minimum allowed length of this input's string.
		@var		$minlength
		@since		20130524
	**/
	public $minlength = null;

	/**
		@details	It should be called min_length, but the attribute in html is without the underscore.
		@param		int			$minlength		Minimum allowed length.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function minlength( $minlength )
	{
		$this->add_validation_method( 'minlength' );
		$this->minlength = intval( $minlength );
		return $this;
	}

	/**
		@details	Checks that the input is of the set minimum length.
		@since		20130524
	**/
	public function validate_minlength()
	{
		if ( strlen( $this->validation_value ) < $this->minlength )
			$this->validation_error()->unfiltered_label( 'The text in %s is too short!', '<em>' . $this->get_label()->content . '</em>' );
	}
}

