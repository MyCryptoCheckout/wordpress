<?php

namespace plainview\sdk_mcc\form2\tests;

/**
	@brief		Test that opt functions work.
	@since		2018-02-07 11:23:46
**/
class OptTest extends TestCase
{
	public function select()
	{
		return $this->form()->select( 'selecttest' )
			->label( 'Select' )
			->opt( 'sel1', 'Select 1' )
			->opt( 'sel2', 'Select %s', 2 )
			->value( 'sel2' );
	}

	/**
		@brief		Test that the correct option is selected.
		@details	This also tests the sprintf.
		@since		2018-02-07 11:27:43
	**/
	public function test_selected()
	{
		$sel = $this->select()->display_input();
		$this->assertStringContainsRegexp( '/\.*\<option.*\<option.*\"selected\".*Select 2.*/s', $sel );
	}

	/**
		@brief		Test retrieval of an option.
		@since		2018-02-07 11:32:38
	**/
	public function test_opt_get()
	{
		$option = $this->select()->opt( 'sel2' );
		$this->assertEquals( $option->get_label()->content, 'Select 2' );
	}
}
