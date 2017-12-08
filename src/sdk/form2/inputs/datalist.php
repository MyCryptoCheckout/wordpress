<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		A datalist containing items for list-aware inputs.
	@author		Edward Plainview <edward@plainview.se>
	@copyright	GPL v3
	@version	20130524
**/
class datalist
	extends input
{
	use traits\options;

	public $self_closing = false;
	public $tag = 'datalist';

	public function _construct()
	{
		$this->hidden();
	}

	/**
		@brief		The value of this "input" is a list of options.
		@return		string		The datalist options.
		@since		20130524
	**/
	public function display_value()
	{
		$r = '';
		foreach( $this->options as $option )
			$r .= $option . "\n";
		return $r;
	}

	public function get_post_value()
	{
	}

	public function new_option( $o )
	{
		$input = new datalistoption( $o->container, $o->name );
		$input->label = $o->label;
		return $input;
	}

	public function use_post_value()
	{
	}

	public function get_value()
	{
	}
}

