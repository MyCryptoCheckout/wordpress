<?php

namespace plainview\sdk_mcc\form2\tests;

class CheckboxTest extends TestCase
{
	public function cb()
	{
		return $this->form()->checkbox( 'checkboxtest' )
			->description( 'A good description' )
			->label( 'Single Checkbox' )
			->value( 'cb_value' );
	}

	public function test_description_exists()
	{
		$cb = $this->cb();
		$this->assertStringContainsRegexp( '/.*\<div.*class="description".*A good description.*/', $cb );
	}
}

