<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		HTML element containing the description of an input.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130929
**/
class description
{
	use \plainview\sdk_mcc\html\element;
	use traits\label;

	public $input;
	public $label;
	public $tag = 'div';

	public function __construct( $input )
	{
		$this->input = $input;
		$this->label = new label( $input );
		$this->container = $this->input->container;
		$this->id( $this->input->make_id() . '_description' );
		$this->css_class( 'description' );
	}

	public function __toString()
	{
		if ( $this->get_label() == '' )
			return '';
		return $this->open_tag() . $this->get_label()->content . $this->close_tag();
	}

	/**
		@brief		Returns the description's input (owner).
		@return		input		The description's input.
		@since		20130524
	**/
	public function input()
	{
		return $this->input;
	}

	/**
		@brief		Is this description empty?
		@return		bool			True if the description is empty.
		@since		20130929
	**/
	public function is_empty()
	{
		return $this->label->is_empty();
	}
}
