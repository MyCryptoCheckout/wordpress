<?php

namespace plainview\sdk_mcc\form2\tests;

class HiddenTest extends TestCase
{
	public function test_input_is_hidden()
	{
		$text = $this->form()->text( 'test' )
			->label( 'Test label' )
			->hidden();
		$this->assertStringContainsRegExp( '/hidden="hidden".*\<input.*hidden="hidden"/s', $text );
	}
}

