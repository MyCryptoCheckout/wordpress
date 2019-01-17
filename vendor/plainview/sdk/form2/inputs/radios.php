<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Input consisting of several radio inputs.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class radios
	extends inputfieldset
{
	public function _construct()
	{
		parent::_construct();
		$this->css_class( 'radios' );
	}

	public function new_option( $o )
	{
		$name = $this->get_name() . '_' . $o->name;
		$input = new radio( $o->container, $name );
		if ( isset( $o->id ) )
			$input->set_attribute( 'id', $o->id );
		if ( isset( $o->label ) )
			$input->label( $o->label );
		$input->set_attribute( 'name', $name );
		$input->set_attribute( 'value', $o->value );
		$input->label->update_for();

		if ( $this->is_required() )
			$input->required();

		return $input;
	}

	/**
		@brief		Check that the input exists in the post.
	**/
	public function validate()
	{
		if ( ! $this->is_required() )
			return $this;

		// Check that the name exists in the post.
		$value = $this->get_post_value();
		if ( $value == null )
		{
			// Find the first option.
			$input = reset( $this->inputs );
			$input->validation_error()->unfiltered_label( 'Please fill in %s.', '<em>' . $this->get_label()->content . '</em>' );
		}
		return $this;
	}
}

