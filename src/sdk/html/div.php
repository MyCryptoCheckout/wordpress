<?php

namespace plainview\sdk_mcc\html;

/**
	@brief		A simple DIV element.
	@details	Exists to allow inline creation of a temp element.
	@since		20130714
**/
class div
{
	use element;

	public $tag = 'div';

	public function __toString()
	{
		return $this->toString();
	}
}

