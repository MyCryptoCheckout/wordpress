<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		A hidden input.
	@details	The value is set using value().
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class hidden
	extends input
{
	use traits\value;

	public $has_description = false;
	public $has_label = false;

	public $type = 'hidden';

	/**
		@brief		Upon construction give the input the hidden attribute.
	**/
	public function _construct()
	{
		return $this->hidden( 'hidden' );
	}

	public function display_label()
	{
		return '';
	}
}

