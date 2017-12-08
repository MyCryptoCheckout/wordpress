<?php

namespace plainview\sdk_mcc\tests;

/**
	@brief		TestCase for Plainview SDK testing.

	@details

	@par		Changelog

	- 20130718	Initial version.

	@since		20130718
	@version	20130718
**/
class TestCase extends \PHPUnit\Framework\TestCase
{
	/**
		@brief		Check if a string contains a substring.
		@since		20130718
	**/
	public function assertStringContains( $needle, $haystack )
	{
		$this->assertTrue( strpos( $haystack, $needle ) !== false );
	}

	/**
		@brief		Check if a string contains a regexp
		@since		20130718
	**/
	public function assertStringContainsRegexp( $regexp, $string )
	{
		$this->assertTrue( $this->string_contains_regexp( $regexp, $string ) );
	}

	/**
		@brief		Check if a string does not contain a substring.
		@since		20130718
	**/
	public function assertStringDoesNotContain( $needle, $haystack )
	{
		$this->assertTrue( strpos( $haystack, $needle ) === false );
	}

	/**
		@brief		Check if a string does not contain a regexp
		@since		20130718
	**/
	public function assertStringDoesNotContainRegexp( $regexp, $string )
	{
		$this->assertFalse( $this->string_contains_regexp( $regexp, $string ) );
	}

	/**
		@brief		Check if a string contains a regexp.
		@since		20130805
	**/
	public function string_contains_regexp( $regexp, $string )
	{
		return preg_match_all( $regexp, $string ) > 0;
	}
}
