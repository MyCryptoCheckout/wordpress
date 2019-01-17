<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		A fieldset / input container.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130805
**/
class fieldset
	extends input
{
	use traits\container;

	public $legend;

	public $self_closing = false;

	public $tag = 'fieldset';

	public function __toString_before_inputs()
	{
		$i = clone( $this );
		// Which one should we display? The legend's label or the fieldset's label?
		if ( $i->label->content != '' )
			$i->legend->label = $i->label;
		return $i->legend;
	}

	public function _construct()
	{
		parent::_construct();
		$this->legend = new legend( $this );
	}

	/**
		@brief		Fieldsets don't have labels.
		@since		20130805
	**/
	public function display_label()
	{
		return '';
	}

	/**
		@brief		Returns the legend attribute.
		@return		legend		The fieldset's legend.
		@since		20130524
	**/
	public function legend()
	{
		return $this->legend;
	}

	public function indentation()
	{
		return $this->form()->indentation() + 1;
	}
}
