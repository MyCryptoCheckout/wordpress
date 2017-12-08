<?php

namespace plainview\sdk_mcc\form2\tests;

class NumberTest extends TestCase
{
	public function test_value_filter()
	{
		$number = $this->form()->number( 'number' )->value( 'a number' );
		$this->assertEquals( '', $number->get_value() );

		$number = $this->form()->number( 'number' )->value( 123 );
		$this->assertEquals( 123, $number->get_value() );
		$number = $this->form()->number( 'number' )->value( 12.3 );
		$this->assertEquals( 12.3, $number->get_value() );
		$number = $this->form()->number( 'number' )->value( -123 );
		$this->assertEquals( -123, $number->get_value() );
	}

	public function test_min()
	{
		$form = $this->form();
		$number = $form->number( 'number' )->min( 500 );
		$_POST[ 'number' ] = 123;
		$form->post();
		$this->assertFalse( $number->validates() );

		$form = $this->form();
		$number = $form->number( 'number' )->min( 500 );
		$_POST[ 'number' ] = 1230;
		$form->post();
		$this->assertTrue( $number->validates() );

		$form = $this->form();
		$number = $form->number( 'number' )->min( -500 );
		$_POST[ 'number' ] = '-1000';
		$form->post();
		$this->assertFalse( $number->validates() );

		$form = $this->form();
		$number = $form->number( 'number' )->min( -500 );
		$_POST[ 'number' ] = '-250';
		$form->post();
		$this->assertTrue( $number->validates() );
	}

	public function test_max()
	{
		$form = $this->form();
		$number = $form->number( 'number' )->max( 500 );
		$_POST[ 'number' ] = 123;
		$form->post();
		$this->assertTrue( $number->validates() );

		$form = $this->form();
		$number = $form->number( 'number' )->max( 500 );
		$_POST[ 'number' ] = 1230;
		$form->post();
		$this->assertFalse( $number->validates() );
	}
}
