<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		A select option.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class selectoption
	extends option
{
	use traits\selected;

	public function __toString()
	{
		$input = clone( $this );
		// Options do not have IDs
		$input->clear_attribute( 'id' );
		return $input->indent() . $input->open_tag() . $input->label->content . $input->close_tag() . "\n";
	}

	public function check( $checked = true )
	{
		$this->selected( $checked );
	}

	public function is_checked()
	{
		return $this->get_attribute( 'selected' );
	}
}
