<?php

namespace plainview\sdk_mcc\form2\validation;

/**
	@brief		A validation error.
	@details

	Uses labels to store the error message.

	Changelog
	---------

	- 20130819	Container is now the input.
	- 20130604	__toString() added.

	@version	20130819
**/
class error
{
	use \plainview\sdk_mcc\form2\inputs\traits\label;

	/**
		@brief		Which input this error belongs to.
		@var		$container
	**/
	public $container;

	public function __construct( $input )
	{
		$this->container = $input;
		$this->label = new \plainview\sdk_mcc\form2\inputs\label( $input );
	}

	public function __toString()
	{
		return $this->get_label()->content;
	}
}
