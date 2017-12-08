<?php

namespace plainview\sdk_mcc\form2\tests;

class PrefixTest extends TestCase
{
	public function test_input_with_prefix()
	{
		$text = $this->form()->text( 'text' )->prefix( 'testprefix' );
		$this->assertStringContains( 'name="testprefix[text]"', $text->display_input() );
	}

	public function test_input_without_prefix()
	{
		$text = $this->form()->text( 'text' );
		$this->assertStringContains( 'name="text"', $text->display_input() );
	}

	public function test_container_with_prefix()
	{
		$select = $this->form()->select( 'select' )->prefix( 'testprefix' );
		$this->assertStringContains( 'name="testprefix[select]"', $select->display_input() );
	}

	public function test_container_without_prefix()
	{
		$select = $this->form()->select( 'select' );
		$this->assertStringContains( 'name="select"', $select->display_input() );
	}

	public function test_multiple_select_with_prefix()
	{
		$select = $this->form()->select( 'select' )->multiple()->prefix( 'testprefix' );
		$this->assertStringContains( 'name="testprefix[select][]"', $select->display_input() );
	}

	public function test_multiple_select_without_prefix()
	{
		$select = $this->form()->select( 'select' )->multiple();
		$this->assertStringContains( 'name="select[]"', $select->display_input() );
	}

	public function test_form_prefix()
	{
		$form = $this->form();
		$form->prefix( 'prefix1' );

		// Add an input without a prefix.
		$form->text( 'text' );
		$this->assertStringContains( 'name="prefix1[text]"', $form );

		// Add an input with its own prefix.
		$form->text( 'text2' )->prefix( 'prefix2' );
		$this->assertStringContains( 'name="prefix1[prefix2][text2]"', $form );
	}
}
