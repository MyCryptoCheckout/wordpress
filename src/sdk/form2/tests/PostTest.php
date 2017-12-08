<?php

namespace plainview\sdk_mcc\form2\tests;

class PostTest extends TestCase
{
	/**
		@brief		Playing with the form attributes.
	**/
	public function testNoPrefix()
	{
		$form = $this->form();
		$text = $form->text( 'test' )
			->value( 'incorrect' );
		// Set the _POST value manually.
		$_POST[ 'test' ] = 'correct';
		$form->post();
		$this->assertEquals( 'correct', $text->get_post_value() );

		// Set a new post value using set_post_value
		$text->set_post_value( 'new value' );
		$this->assertEquals( 'new value', $text->get_post_value() );
	}

	public function testPrefix()
	{
		$form = $this->form();
		$text = $form->text( 'test' )
			->prefix( 'greatprefix' )
			->value( 'incorrect' );
		// Set the _POST value manually.
		$_POST[ 'greatprefix' ][ 'test' ] = 'correct';
		$form->post();
		$this->assertEquals( 'correct', $text->get_post_value() );

		// Set a new post value using set_post_value
		$text->set_post_value( 'newer value' );
		$this->assertEquals( 'newer value', $text->get_post_value() );
	}
}
