<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		A fieldset for checkboxes.
	@details

	The checkboxes are placed in a fieldset with a legend.

	The legend is from the checkboxes input's label.

	@par		Usage

	$cbs = $form->checkboxes( 'checkboxestest' )
		->label( 'Your sex' )
		->option( 'Male', 'm' )
		->option( 'Female', 'f' )
		->value( 'm' );

	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130820
**/
class checkboxes
	extends inputfieldset
{
	public function _construct()
	{
		parent::_construct();
		$this->css_class( 'checkboxes' );
	}

	/**
		@brief		Convenience method to retrieve an array of all of the checked values.
		@return		array		An array of values from the checked checkboxes.
		@since		20130820
	**/
	public function get_post_value()
	{
		$r = [];
		foreach( $this->get_options() as $index => $option )
		{
			$cb = clone( $option );
			$cb->use_post_value();
			if ( $cb->is_checked() )
				$r[] = $cb->get_value();
		}
		return $r;
	}

	/**
		@brief		Create a new checkbox option.
		@param		object			$o		Options.
		@return		checkbox		Newly-created checkbox.
		@see		\\plainview\\sdk_mcc\\form2\\inputs\\traits\\options::option()
		@since		20130524
	**/
	public function new_option( $o )
	{
		$input = new checkbox( $o->container, $o->name );
		if ( isset( $o->id ) )
			$input->set_attribute( 'id', $o->id );
		if ( isset( $o->label ) )
			$input->label( $o->label );
		if ( isset( $o->name ) )
		{
			$input->set_attribute( 'name', $this->get_name() . '_' . $o->name );
		}
		else
			$input->set_attribute( 'name', $o->value );
		$input->set_value( $o->name );
		$input->label->update_for();
		return $input;
	}


	/**
		@brief		Tell each checkbox to use the post value.
		@since		20130524
	**/
	public function use_post_value()
	{
		$name = $this->get_name();
		foreach( $this->get_options() as $index => $option )
		{
			$cb = clone( $option );
			// Clear the check
			$cb->check( false );
			// And now set it according to the post
			$cb->use_post_value();
			$option->check( $cb->is_checked() );
		}
	}
}

