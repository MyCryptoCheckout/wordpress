<?php

namespace plainview\sdk_mcc\form2\tests;

class SubmitTest extends TestCase
{
	/**
		@brief		Testing submitting.
	**/
	public function test_submit()
	{
		$form = $this->form();
		$form->submit( 'test' )
			->value( 'Test value' );

		// Button not pressed yet.
		$this->assertFalse( $form->input( 'test' )->pressed() );

		$form->post();
		$form->set_post_value( 'test', 'Test value' );
		// Now it's pressed.
		$this->assertTrue( $form->input( 'test' )->pressed() );
	}
}
