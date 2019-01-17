<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		A meter input, showing high / low /optimum values.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class meter
	extends number
{
	use traits\high;
	use traits\low;
	use traits\optimum;

	public $self_closing = false;
	public $type = 'meter';
}

