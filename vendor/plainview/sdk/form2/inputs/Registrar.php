<?php

namespace plainview\sdk_mcc\form2\inputs;

/**
	@brief		Handles registration of input types.
	@details	Does nothing much more than handle the input type array.
	@since		2022-07-17 12:23:08
**/
class Registrar
{
	/**
		@brief		The form we belong to.
		@since		2022-07-17 12:15:22
	**/
	public $form;

	/**
		@brief		Constructor.
		@since		2022-07-17 12:14:34
	**/
	public function __construct( $form )
	{
		$this->form = $form;
	}

	/**
		@brief		Register this input type.
		@since		2022-07-17 12:17:25
	**/
	public function add( $input_name, $input_class )
	{
		$this->form->input_types[ $input_name ] = (object) [
			'class' => $input_class,
		];
		return $this;
	}

	/**
		@brief		Deregister this input.
		@details	Basically a convenience method.
		@since		2022-07-17 12:21:27
	**/
	public function remove( $name )
	{
		unset( $this->form->input_types[ $name ] );
		return $this;
	}
}
