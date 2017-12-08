<?php

namespace plainview\sdk_mcc\form2\tests;

/**
	@brief		Test the date input, including some raw post values.
	@since		2014-07-11 23:31:28
**/
class DateTest extends TestCase
{
	/**
		@brief		Return a date input.
		@since		2014-07-11 23:18:33
	**/
	public function date()
	{
		$this->form = $this->form();
		$date = $this->form->date( 'datetest' )
			->label( 'Date test' );
		return $date;
	}

	public function test_empty_input()
	{
		$date = $this->date();
		$_POST = [ 'datetest' => '' ];
		$this->form->post( [ 'datetest' => '' ] );
		$this->assertEquals( $date->get_post_value(), '1970-01-01' );
		$this->assertEquals( $date->get_raw_post_value(), '' );
	}

	public function test_bad_date()
	{
		$date = $this->date();
		$_POST = [ 'datetest' => 'xyz' ];
		$this->form->post();
		$this->assertEquals( $date->get_post_value(), '1970-01-01' );
	}

	public function test_good_date()
	{
		$date = $this->date();
		$_POST = [ 'datetest' => '2014-01-20' ];
		$this->form->post();
		$this->assertEquals( $date->get_post_value(), '2014-01-20' );
	}
}
