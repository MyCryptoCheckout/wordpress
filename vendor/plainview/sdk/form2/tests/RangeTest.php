<?php

namespace plainview\sdk_mcc\form2\tests;

/**
	@brief		Test ranges.
	@since		2017-10-19 23:52:05
**/
class RangeTest extends TestCase
{
	public function range()
	{
		return $this->form()->range( 'rangetest' )
			->label( 'Test of Range' )
			->value( 100 );
	}

	/**
		@brief		Try setting minimum and maximum.
		@since		2017-10-19 23:52:40
	**/
	public function test_min_max()
	{
		$input = $this->range();
		$input->min( 100 );
		$input->max( 500 );
		$this->assertStringContainsRegExp( '/min="100"/', $input->display_input() );
		$this->assertStringContainsRegExp( '/max="500"/', $input->display_input() );
	}

	/**
		@brief		Test step.
		@since		2017-10-19 23:52:40
	**/
	public function test_step()
	{
		$input = $this->range();
		$input->step( 12 );
		$this->assertStringContainsRegExp( '/step="12"/', $input->display_input() );
		$input->step( 0.05 );
		$this->assertStringContainsRegExp( '/step="0.05"/', $input->display_input() );
		$input->step( 'x5' );
		$this->assertStringContainsRegExp( '/step="0"/', $input->display_input() );
	}
}
