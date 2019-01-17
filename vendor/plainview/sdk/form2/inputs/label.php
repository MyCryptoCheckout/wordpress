<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		HTML element being the label of an input.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130929
**/
class label
{
	use \plainview\sdk_mcc\html\element;

	public $input;

	public $tag = 'label';

	public function __construct( $input )
	{
		$this->set_input( $input );
		$this->container = $this->input->container;
		$this->update_for();
	}

	public function __toString()
	{
		return $this->toString();
	}

	/**
		@brief		Returns the label's input (owner).
		@return		input		The description's input.
		@since		20130703
	**/
	public function input()
	{
		return $this->input;
	}

	/**
		@brief		Is this label empty?
		@return		bool		True if the label is empty.
		@since		20130929
	**/
	public function is_empty()
	{
		return $this->content == '';
	}

	/**
		@brief		Update's the label's for attribute.
		@return		this		Method chaining.
		@since		20130709
	**/
	public function update_for()
	{
		$this->set_attribute( 'for', $this->input->get_attribute( 'id' ) );
		return $this;
	}

	/**
		@brief		Set this label's input object.
		@details	Will automatically update the for attribute.
		@param		input		The new input for this label.
		@return		this		Method chaining.
		@since		20130709
	**/
	public function set_input( $input )
	{
		$this->input = $input;
		$this->update_for();
		return $this;
	}
}
