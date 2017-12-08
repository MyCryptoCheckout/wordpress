<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		An option belonging to a datalist.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class datalistoption
	extends option
{
	public $self_closing = true;

	public function __toString()
	{
		$option = clone( $this );
		$option->clear_attribute( 'name' );
		$option->set_attribute( 'value', $option->label );
		return $option->open_tag();
	}

	public function check()
	{
	}

	public function is_checked()
	{
	}
}

