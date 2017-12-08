<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Fieldset legend.
	@details	An HTML element beloning to a fieldset.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class legend
	extends input
{
	use traits\label;

	public $label;

	public $self_closing = false;

	public $tag = 'legend';

	public function __construct( $fieldset )
	{
		$this->container = $fieldset;
		$this->fieldset = $fieldset;
		$this->label = new label( $this );
	}

	public function __toString()
	{
		if ( $this->label->content == '' )
			return '';
		return $this->indent() . $this->open_tag() . $this->label->content . $this->close_tag() . "\n";
	}

	/**
		@brief		Return the parent fieldset for chaining.
		@return		fieldset		The parent fieldset.
		@since		20130524
	**/
	public function fieldset()
	{
		return $this->fieldset;
	}

	public function indentation()
	{
		return $this->fieldset->indentation() + 1;
	}
}

