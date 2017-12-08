<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		A select's option group.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130730
**/
class selectoptgroup
	extends optionsinput
	implements \Countable
{
	public $self_closing = false;
	public $tag = 'optgroup';

	public function __toString()
	{
		$optgroup = clone( $this );
		$optgroup->set_attribute( 'label', $this->get_label()->content );
		$optgroup->clear_attribute( 'name' );
		return $optgroup->open_tag() . "\n" . $optgroup->display_input() . $optgroup->close_tag() . "\n";
	}

	public function check( $checked )
	{
		foreach( $this->options as $option )
			$option->check( $checked );
	}

	/**
		@brief		Count how many options this optgroup has.
		@return		int		The amount of options this optgroup has.
		@since		20130730
	**/
	public function count()
	{
		return count( $this->options );
	}

	public function display_input()
	{
		$r = '';
		foreach( $this->options as $option )
		{
			$option = clone( $option );
			$option->clear_attribute( 'name' );
			if ( in_array( $option->get_attribute( 'value' ), $this->container->_value ) )
				$option->check( true );
			$r.= $option;
		}
		return $r;
	}

	public function new_option( $o )
	{
		$input = new selectoption( $o->container, $o->name );
		$input->label( $o->label );
		$input->value( $o->value );
		return $input;
	}

	/**
		@brief		Return the parent select class, to enable object chaining.
		@return		select		Parent select class.
		@since		20130524
	**/
	public function select()
	{
		return $this->container;
	}
}

