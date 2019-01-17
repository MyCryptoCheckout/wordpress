<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Option class.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
abstract class option
	extends input
{
	use traits\label;
	use traits\value;

	public $container;
	public $self_closing = false;
	public $tag = 'option';

	public abstract function check();

	public function indentation()
	{
		return $this->container->indentation() + 1;
	}

	public abstract function is_checked();

	public function container()
	{
		return $this->container;
	}

	public function use_post_value()
	{
		$value = $this->form()->get_post_value( $this->make_name() );
		$this->set_attribute( 'value', $value );
		return $this;
	}
}
