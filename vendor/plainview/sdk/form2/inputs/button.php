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

	/**
		@brief		The tag for this input.
		@since		2022-07-17 13:51:45
	**/
	public $tag = 'button';

	/**
		@brief		The type of input.
		@since		2022-07-17 13:53:59
	**/
	public $type = 'button';

	/**
		@brief		Buttons do not self close.
		@since		2022-07-17 20:41:05
	**/
	public $self_closing = false;

	/**
		@brief		Buttons don't have labels.
		@since		20130524
	**/
	public function display_label()
	{
		return '';
	}

	/**
		@brief		Show the value.
		@since		2022-07-17 13:21:03
	**/
	public function display_value()
	{
		return $this->get_value();
	}

	/**
		@brief		Buttons don't care about posts.
		@since		20130524
	**/
	public function use_post_value()
	{
	}
}

