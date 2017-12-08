<?php

namespace plainview\sdk_mcc\form2\inputs;

class inputfieldset
	extends fieldset
{
	use traits\options;
	use traits\value
	{
		traits\options::use_post_value insteadof traits\value;
		traits\options::value insteadof traits\value;
	}

	/**
		@brief		Fieldsets do not have names.
		@since		2014-11-16 12:59:23
	**/
	public function __toString_before_container()
	{
		$this->__name = $this->get_attribute( 'name' );
		$this->clear_attribute( 'name' );
		return parent::__toString_before_container();
	}

	/**
		@brief		Fieldsets do not have names.
		@since		2014-11-16 12:59:23
	**/
	public function __toString_before_inputs()
	{
		$this->set_attribute( 'name', $this->__name );
		unset( $this->__name );
		return parent::__toString_before_inputs();
	}

	public function add_option( $input )
	{
		return $this->add_input( $input );
	}

	/**
		@brief		Input fieldsets don't have labels. They have just a fieldset and contents.
		@since		20130805
	**/
	public function display_label()
	{
		return '';
	}

	public function get_option( $name )
	{
		return $this->input( $name );
	}

	public function get_options()
	{
		return $this->inputs;
	}
}
