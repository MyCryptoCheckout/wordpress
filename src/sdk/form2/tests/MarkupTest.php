<?php

namespace plainview\sdk_mcc\form2\tests;

/**
	@brief		Test the markup input.
	@since		2018-02-07 12:40:26
**/
class MarkupTest extends TestCase
{
	/**
		@brief		Test the p function.
		@since		2018-02-07 12:40:45
	**/
	public function test_p()
	{
		$form = $this->form();
		$markup = $form->markup( 'markuptest' );

		$markup->p( 'Hello 123' );
		$this->assertStringContains( '<p>Hello 123</p>', $markup->display_input() );

		$markup->p( 'Hello %s', '321' );
		$this->assertStringContains( '<p>Hello 321</p>', $markup->display_input() );

		$markup->markup( 'All good!' );
		$this->assertEquals( 'All good!', $markup->display_input() );
	}
}
