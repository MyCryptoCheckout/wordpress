<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		A checkbox input.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class checkbox
	extends option
{
	use traits\checked;

	public $self_closing = true;
	public $tag = 'input';
	public $type = 'checkbox';

	public function _construct()
	{
		// A checkbox needs a value, else it won't exist in the POST array even if checked.
		$this->value( 'on' );
	}

	/**
		@brief		Input before label.
	**/
	public function assemble_input_string( $o )
	{
		$r = '';
		$r .= $o->indent . '<div class="input_container">' . $o->input . "</div>\n";
		$r .= $o->indent . '<div class="label_container">' . $o->label . "</div>\n";
		if ( isset( $o->description ) )
			$r .= $o->indent . '<div class="description_container">' . $o->description . "</div>\n";
		return $r;
	}

	public function check( $check = true)
	{
		$check = $check ? 'checked' : '';
		return $this->checked( $check );
	}

	public function use_post_value()
	{
		// It is checked if the name exists in the post AND the value there is the same as this one.
		$value = $this->get_post_value();
		$this->check( $value == $this->get_value() );
		return $this;
	}
}

