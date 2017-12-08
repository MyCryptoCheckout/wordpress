<?php

namespace plainview\sdk_mcc\form2\inputs;

class optionsinput
	extends input
{
	use traits\options;

	public $options = [];

	public function add_option( $option )
	{
		$name = $option->get_name();
		$this->options[ $name ] = $option;
		return $this;
	}

	public function get_option( $name )
	{
		return ( isset( $this->options[ $name ] ) ? $this->options[ $name ] : false );
	}

	public function get_options()
	{
		return $this->options;
	}

	/**
		@brief		Sort the options.
		@since		2016-01-12 22:25:10
	**/
	public function sort_inputs()
	{
		// The sort_inputs() method from the sort_order trait requires inputs, which a select doesn't have.
		$this->inputs = $this->options;
		parent::sort_inputs();
		$this->options = $this->inputs;
		return $this;
	}

}