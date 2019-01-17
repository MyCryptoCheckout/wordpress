<?php

namespace plainview\sdk_mcc\form2\tests;

class InputTest extends TestCase
{
	public function test_plaintext_description()
	{
		$text = $this->text()
			->description( 'A nice description' );
		$this->assertStringContainsRegexp( '/.*\<div.*class="description".*A nice description.*/', $text );
	}

	public function test_description_with_html()
	{
		$text = $this->text()
			->description( 'A <h1>bad</h1> description' );
		$this->assertStringContainsRegexp( '/.*\<div.*class="description".*A &lt;h1&gt;bad&lt;\/h1&gt; description.*/', $text );
	}

	public function test_input_described_by()
	{
		$text = $this->text()
			->description( 'A <h1>bad</h1> description' );
		$this->assertStringContainsRegexp( '/\<input.*aria-describedby=\".*\<div.*class=\"description\"/s', $text );
	}

	public function test_container_contains_extra_css_classes()
	{
		$css_class = 'testing_css';
		$text = $this->text()
			->css_class( $css_class );
		$this->assertStringContainsRegexp( '/\<div.*class.*' . $css_class . '.*\<input.*class.*' . $css_class . '/s', $text );
	}

	/**
		@brief		Test a placeholder.
		@since		2018-02-07 11:40:14
	**/
	public function test_placeholder()
	{
		$input = $this->form()->text( 'placeholdertest' );
		$input->placeholder( 'This is %s', 'great' );
		$this->assertStringContains( 'This is great', $input->display_input() );

		$input->set_placeholder( 'This is bad' );
		$this->assertStringContains( 'This is bad', $input->display_input() );
	}

	public function text()
	{
		return $this->form()->text( 'text_test' );
	}
}
