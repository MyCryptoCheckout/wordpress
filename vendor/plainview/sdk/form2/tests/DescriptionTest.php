<?php

namespace plainview\sdk_mcc\form2\tests;

class DescriptionTest extends TestCase
{
	public function input()
	{
		return $this->form()->text( 'testtext' )
			->label( 'With description' );
	}

	public function test_with_description()
	{
		$description = 'This is a good looking description';
		$input = $this->input()
			->description( $description );
		$this->assertFalse( $input->description->is_empty() );
		$this->assertStringContainsRegExp( '/.*class="description.*' . $description . '.*/', $input );

		// Test sprintf.
		$input->description( 'Test 1 %s 3', 2 );
		$this->assertStringContainsRegExp( '/.*Test 1 2 3.*/', $input );

		$description = 'This is a %s looking description';
		$input->description( $description, 'better' );
		$this->assertStringContainsRegExp( '/.*class="description.*This is a better looking description.*/', $input );
	}

	public function test_without_description()
	{
		$input = $this->input();
		$this->assertTrue( $input->description->is_empty() );
		$this->assertStringDoesNotContainRegexp( '/.*class="description/', $input );
	}
}

