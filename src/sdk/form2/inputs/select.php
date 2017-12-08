<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Select input.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20131109
**/
class select
	extends optionsinput
	implements \Countable
{
	use traits\size;
	use traits\value, traits\options
	{
		traits\options::use_post_value insteadof traits\value;
		traits\options::value insteadof traits\value;
	}

	public $self_closing = false;
	public $tag = 'select';

	public $_value = array();

	public function __toString()
	{
		$div = $this->get_display_div();
		$div->content = $this->indent() . $this->display_label() . $this->display_input();
		return $div . '';
	}

	/**
		@brief		Sets the size of the select to fit the amount of options.
		@return		$this		Method chaining.
		@since		20130730
	**/
	public function autosize()
	{
		return $this->size( count( $this ) );
	}

	/**
		@brief		Count how many optgroups and options this select has.
		@details

		Counting includes optgroups and the options of each group (hence the +1).

		@return		int		A count of options and optgroups.
		@since		20130730
	**/
	public function count()
	{
		$c = 0;
		foreach( $this->options as $option )
			if ( is_a( $option, 'plainview\\sdk_mcc\\form2\\inputs\\selectoptgroup' ) )
				$c += count( $option ) + 1;
			else
				$c++;
		return $c;
	}

	public function display_input()
	{
		$input = clone( $this );

		$input->css_class( 'select' );

		if ( $input->is_required() )
			$input->css_class( 'required' );

		$input->set_attribute( 'name', $input->make_name() );

		$r = $input->indent() . $input->open_tag() . "\n";
		foreach( $input->options as $option )
		{
			$option = clone( $option );
			if ( is_a( $option, 'plainview\\sdk_mcc\\form2\\inputs\\selectoptgroup' ) )
				$r .= $option;
			else
			{
				$option->clear_attribute( 'name' );
				if ( in_array( $option->get_attribute( 'value' ), $input->_value ) )
					$option->check( true );
				$r.= $option;
			}
		}
		$r .= $input->indent() . $input->close_tag() . "\n";
		return $r;
	}

	/**
		@brief		Returns the input's value from the _POST variable.
		@details	Will strip off slashes before returning the value.
		@return		string		The value of the _POST variable. If no value was in the post, null is returned.
		@see		use_post_value()
		@since		20130524
	**/
	public function get_post_value()
	{
		$name = $this->make_name();
		if ( $this->is_multiple() )
			$name = substr( $name, 0, -2 );
		$r = $this->form()->get_post_value( $name );
		if ( $this->is_multiple() && ! $r )
			$r = [];
		return $r;
	}
	/**
		@brief		Return if the user may select multiple options.
		@return		bool		True if the multiple attribute is set.
		@since		20130506
	**/
	public function is_multiple()
	{
		return $this->get_boolean_attribute( 'multiple' );
	}

	/**
		@brief		Make the name of the input and maybe correct for multiplicity.
		@return		string		The HTML name of the input.
	**/
	public function make_name()
	{
		$name = parent::make_name();
		if ( $this->is_multiple() )
			$name .= '[]';
		return $name;
	}

	/**
		@brief		Allow the user to select several options.
		@param		bool		$multiple		True if the user is allowed to select multiple options.
		@return		$this		This object.
		@since		20130524
	**/
	public function multiple( $multiple = true )
	{
		return $this->set_boolean_attribute( 'multiple', $multiple );
	}

	public function new_option( $o )
	{
		$input = new selectoption( $o->container, $o->name );
		$input->set_attribute( 'value', $o->value );
		$input->label( $o->label );
		return $input;
	}

	/**
		@brief		Create / return an optgroup.
		@param		string		$name		Name of the optgroup to create / return.
		@return		optgroup		Created or returned optgroup.
		@since		20130524
	**/
	public function optgroup( $name )
	{
		if ( isset( $this->options[ $name ] ) )
			return $this->options[ $name ];
		$input = new selectoptgroup( $this, $name );
		$this->options[ $name ] = $input;
		return $input;
	}

	/**
		@brief		Set the value of this select.
		@details	Several parameters can be given and they will be merged into an array.
		@param		mixed		$value		Value to set.
		@return		$this		This object.
	**/
	public function value( $value, $value2 = null )
	{
		$args = func_get_args();
		if ( count( $args ) > 1 )
			$value = $args;
		if ( ! is_array( $value ) )
			$value = array( $value );
		$this->_value = $value;
		return $this;
	}
}

