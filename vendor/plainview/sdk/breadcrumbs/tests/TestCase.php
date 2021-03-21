<?php

namespace plainview\sdk_mcc\breadcrumbs\tests;

/**
	@brief		TestCase for Form2 testing.

	@details

	@par		Changelog

	- 20130718	Initial version.

	@since		20130729
	@version	20130729
**/
class TestCase extends \plainview\sdk_mcc\tests\TestCase
{
	/**
		@brief		Create the breadcrumbs object.
		@return		\plainview\sdk_mcc\breadcrumbs\breadcrumbs		Newly-created breadcrumbs.
		@since		20130729
	**/
	public function bcs()
	{
		return new \plainview\sdk_mcc\breadcrumbs\breadcrumbs;
	}
}
