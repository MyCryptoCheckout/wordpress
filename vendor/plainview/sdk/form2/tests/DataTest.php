<?php

namespace plainview\sdk_mcc\form2\tests;

/**
	@brief		Test the getting and setting of data.
	@since		2023-11-30 20:59:16
**/
class DataTest extends TestCase
{
	/**
		@brief		Temporary form variable.
		@since		2023-03-21 12:12:23
	**/
	public $form;

	public function test_data()
	{
		$this->form = $this->form();
		$input = $this->form->text( 'datatest' )
			->label( 'Data test' );
		$key = md5( time() . rand( 0, time() ) );
		$value = md5( time() . rand( 0, time() ) );
		$input->data( $key, $value );

		$this->assertTrue( $input->has_attribute( 'data-' . $key ) );

		$this->assertEquals( $input->data( $key ), $value );
	}
}
