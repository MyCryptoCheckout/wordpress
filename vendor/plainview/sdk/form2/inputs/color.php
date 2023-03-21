<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		The HTML5 color picker.
	@since		2023-02-25 11:07:24
**/
class color
	extends input
{
	use traits\value;


	/**
		@brief		The tag of the input.
		@since		2023-02-25 11:07:47
	**/
	public $type = 'color';
}
