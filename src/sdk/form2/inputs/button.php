<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Button input.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class button
	extends input
{
	use traits\label;
	use traits\value;

	public $type = 'button';

	/**
		@brief		Buttons don't have labels.
		@since		20130524
	**/
	public function display_label()
	{
		return '';
	}

	/**
		@brief		Buttons don't care about posts.
		@since		20130524
	**/
	public function use_post_value()
	{
	}
}

