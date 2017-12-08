<?php

namespace plainview\sdk_mcc\form2\tests;

class TitleTest extends TestCase
{
	public function test_input_gets_title()
	{
		$text = $this->text();
		$text->title( 'This is a great title' );
		$this->assertStringContainsRegExp( '/\<input.*title="This is a great title"/s', $text );

		$text->title( 'This is a <h1>great title' );
		$this->assertStringContainsRegExp( '/\<input.*title="This is a &lt;h1&gt;great title"/s', $text );
	}

	public function test_label_gets_title()
	{
		$text = $this->text();
		$text->title( 'This is a great title' );
		$this->assertStringContainsRegExp( '/\<label.*title="This is a great title".*\<input.*title="This is a great title"/s', $text );

		$text->title( 'This is a <h1>great title' );
		$this->assertStringContainsRegExp( '/\<label.*title="This is a &lt;h1&gt;great title".*\<input.*title="This is a &lt;h1&gt;great title"/s', $text );
	}

	public function text()
	{
		return $this->form()->text( 'testtext' )
			->label( 'Test input' );
	}
}
