<?php

namespace plainview\sdk_mcc\form2\tests;

class TextTest extends TestCase
{
	public function test_input()
	{
		$text = $this->form()->text( 'text' );
		$this->assertInstanceOf( '\\plainview\\sdk_mcc\\form2\\inputs\\text', $text );
	}

	public function test_lowercase()
	{
		$text = $this->form()->text( 'lowercase' )->lowercase();
		$text->value( 'SMÖRGÅSBORD' );
		$this->assertEquals( 'smörgåsbord', $text->get_value() );
	}

	public function test_minlength()
	{
		// Make a string that is too short.
		$form = $this->form();
		$text = $form->text( 'minlength' )->minlength( 3 );
		$_POST[ 'minlength' ] = '12';
		$form->post();
		$this->assertFalse( $text->validates() );

		// Make a string that is long enough.
		$form = $this->form();
		$text = $form->text( 'minlength' )->minlength( 3 );
		$_POST[ 'minlength' ] = '123';
		$form->post();
		$this->assertTrue( $text->validates() );
	}

	public function test_minlength_trim()
	{
		// This trimmed string should be too short
		$form = $this->form();
		$text = $form->text( 'minlength' )->trim()->minlength( 3 );
		$_POST[ 'minlength' ] = ' 12';
		$form->post();
		$this->assertFalse( $text->validates() );

		// This too should be too short.
		$form = $this->form();
		$text = $form->text( 'minlength' )->trim()->minlength( 3 );
		$_POST[ 'minlength' ] = ' 12 ';
		$form->post();
		$this->assertFalse( $text->validates() );

		// This one should be just fine.
		$form = $this->form();
		$text = $form->text( 'minlength' )->trim()->minlength( 3 );
		$_POST[ 'minlength' ] = ' 123 ';
		$form->post();
		$this->assertTrue( $text->validates() );
	}

	public function test_maxlength()
	{
		// Make a short string.
		$form = $this->form();
		$text = $form->text( 'maxlength' )->maxlength( 3 );
		$_POST[ 'maxlength' ] = '12';
		$form->post();
		$this->assertTrue( $text->validates() );

		// Make a string that is just right.
		$form = $this->form();
		$text = $form->text( 'maxlength' )->maxlength( 3 );
		$_POST[ 'maxlength' ] = '123';
		$form->post();
		$this->assertTrue( $text->validates() );

		// Too long!
		$form = $this->form();
		$text = $form->text( 'maxlength' )->maxlength( 3 );
		$_POST[ 'maxlength' ] = '1234';
		$form->post();
		$this->assertFalse( $text->validates() );
	}

	public function test_maxlength_trim()
	{
		// Spaces on both sides :)
		$form = $this->form();
		$text = $form->text( 'maxlength' )->trim()->maxlength( 3 );
		$_POST[ 'maxlength' ] = ' 123 ';
		$form->post();
		$this->assertTrue( $text->validates() );

		// Spaces + 4 on both sides :(
		$form = $this->form();
		$text = $form->text( 'maxlength' )->trim()->maxlength( 3 );
		$_POST[ 'maxlength' ] = ' 1234 ';
		$form->post();
		$this->assertFalse( $text->validates() );
	}

	public function test_required()
	{
		// First: not required :)
		$form = $this->form();
		$text = $form->text( 'required' );
		$form->post();
		$this->assertTrue( $text->validates() );

		// Second: required :(
		$form = $this->form();
		$text = $form->text( 'required' )->required();
		$form->post();
		$this->assertFalse( $text->validates() );
	}

	public function test_trim()
	{
		$text = $this->form()->text( 'trim' )->trim();
		$text->value( ' spaces ' );
		$this->assertEquals( 'spaces', $text->get_value() );
	}

	public function test_uppercase()
	{
		$text = $this->form()->text( 'uppercase' )->uppercase();
		$text->value( 'smörgåsbord' );
		$this->assertEquals( 'SMÖRGÅSBORD', $text->get_value() );
	}

	public function test_plaintext_filter()
	{
		$text = $this->form()->text( 'plaintext' )->plaintext()->value( '<h1>Great</h1>' );
		$this->assertEquals( 'Great', $text->get_value() );
		$text = $this->form()->text( 'plaintext' )->plaintext()->value( '<h1>Gr&eat</h1>' );
		$this->assertEquals( 'Gr&amp;eat', $text->get_value() );
	}

	public function test_unfiltered_value()
	{
		$string = '<h1>Great</h1>';
		$text = $this->form()->text( 'unfiltered' )->set_unfiltered_value( $string  );
		$this->assertEquals( $string, $text->get_value() );
	}
}
