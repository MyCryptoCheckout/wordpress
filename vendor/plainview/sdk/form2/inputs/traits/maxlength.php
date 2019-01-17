<?php

namespace plainview\sdk_mcc\form2\inputs\traits;

/**
	@brief		Manipulate the maxlength attribute.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
trait maxlength
{
	/**
		@brief		Sets the input's maxlength value.
		@param		int			$maxlength		The input's new maxlength value.
		@return		this		Object chaining.
		@since		20130524
	**/
	public function maxlength( $maxlength )
	{
		$this->add_validation_method( 'maxlength' );
		$maxlength = intval( $maxlength );
		return $this->set_attribute( 'maxlength', $maxlength );
	}

	public function validate_maxlength()
	{
		$maxlength = $this->get_attribute( 'maxlength' );
		if ( strlen( $this->validation_value ) > $maxlength )
			$this->validation_error()->unfiltered_label( 'The text in %s is too long!', '<em>' . $this->get_label()->content . '</em>' );
	}
}

