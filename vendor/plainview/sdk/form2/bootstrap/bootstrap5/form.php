<?php

namespace plainview\sdk_mcc\form2\bootstrap\bootstrap5;

/**
	@brief		The bootstrap 5 version of the form.
	@since		2022-07-17 12:02:09
**/
class form
	extends \plainview\sdk_mcc\form2\bootstrap\form
{
	/**
		@brief		Register all the input types we know of.
		@since		2022-07-17 12:11:53
	**/
	public function register_input_types()
	{
		parent::register_input_types();
		$registrar = $this->input_registrar();
		$registrar->add( 'button', __NAMESPACE__ . '\\inputs\\button' );
		$registrar->add( 'submit', __NAMESPACE__ . '\\inputs\\submit' );
		return $this;
	}
}
