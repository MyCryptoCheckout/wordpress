<?php

namespace plainview\sdk_mcc\form2\tests;

class CheckboxesTest extends TestCase
{
	public function checkboxes()
	{
		return $this->form()->checkboxes( 'checkboxestest' )
			->label( 'Checkboxes' )
			->option( 'Checkbox 1', 'cb1' )
			->option( 'Checkbox 2', 'cb2' )
			->option( 'Checkbox 3', 'cb3' )
			->value( 'cb2' );
	}

	public function test_ids()
	{
		$cbs = $this->checkboxes();
		$this->assertStringContainsRegExp( '/id=".*checkboxestest_cb1"/', $cbs );
		$this->assertStringContainsRegExp( '/id=".*checkboxestest_cb2"/', $cbs );
		$this->assertStringContainsRegExp( '/id=".*checkboxestest_cb3"/', $cbs );
		$this->assertStringDoesNotContainRegExp( '/input.*id=".*checkboxestest"/', $cbs );
	}

	public function test_names()
	{
		$cbs = $this->checkboxes();
		$this->assertStringContains( 'name="checkboxestest_cb1"', $cbs );
		$this->assertStringContains( 'name="checkboxestest_cb2"', $cbs );
		$this->assertStringContains( 'name="checkboxestest_cb3"', $cbs );
		$this->assertStringDoesNotContainRegExp( '/\<input.*name="checkboxestest"/', $cbs );
	}

	public function test_named_checkbox()
	{
		$cbs = $this->checkboxes();
		// Try to extract a named checkbox out of the checkboxes.
		// Here we know that the checkboxes input is actually a fieldset and that all checkboxes are named CHECKBOXESNAME_CHECKBOXNAME.
		// Meaning: checkboxestest_cb1 instead of just cb1.
		$label = $cbs->input( 'checkboxestest_cb1' )->get_label()->get_content();
		$this->assertEquals( 'Checkbox 1', $label );
	}

	public function test_checked()
	{
		$cbs = $this->checkboxes();
		$this->assertStringContainsRegexp( '/\.*\<input.*\<input.*checked=\"checked\".*cb3.*\<input/s', $cbs );
	}

	public function test_labels()
	{
		$cbs = $this->checkboxes();
		$this->assertStringContains( '<legend>Checkboxes</legend>', $cbs );
		$this->assertStringContainsRegExp( '/\<label.*for=".*checkboxestest_cb1".*Checkbox 1.*\/label\>/', $cbs );
	}

	public function test_prefix_is_inherited_to_each_checkbox()
	{
		$cbs = $this->checkboxes();
		$cbs->prefix( 'testprefix' );
		$matches = preg_match_all( '/name="testprefix\[checkboxestest_/', $cbs );
		$this->assertEquals( 3, $matches );
	}

	public function test_prefixes_are_inherited_to_each_checkbox()
	{
		$cbs = $this->checkboxes();
		$cbs->prefix( 'testprefix1', 'testprefix2' );
		$matches = preg_match_all( '/name="testprefix1\[testprefix2\]\[checkboxestest_/', $cbs );
		$this->assertEquals( 3, $matches );
	}

	public function test_post_value()
	{
		$cbs = $this->checkboxes();
		$form = $cbs->form();
		$form->post( [
			'checkboxestest_cb1' => 'cb1',
			'checkboxestest_cb3' => 'cb3',
		] )->use_post_values();
		$this->assertTrue( $cbs->input( 'checkboxestest_cb1' )->is_checked() );
		$this->assertFalse( $cbs->input( 'checkboxestest_cb2' )->is_checked() );
		$this->assertTrue( $cbs->input( 'checkboxestest_cb3' )->is_checked() );
		$this->assertEquals( [ 'cb1', 'cb3' ], $cbs->get_post_value() );
	}

	public function test_post_value_with_prefix()
	{
		$cbs = $this->checkboxes()->prefix( 'goodprefix' );
		$form = $cbs->form();
		$form->post( [
			'goodprefix' =>
			[
				'checkboxestest_cb1' => 'cb1',
				'checkboxestest_cb3' => 'cb3',
			]
		] )->use_post_values();
		$this->assertTrue( $cbs->input( 'checkboxestest_cb1' )->is_checked() );
		$this->assertFalse( $cbs->input( 'checkboxestest_cb2' )->is_checked() );
		$this->assertTrue( $cbs->input( 'checkboxestest_cb3' )->is_checked() );
	}
}

