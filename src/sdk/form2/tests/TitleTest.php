<?php

namespace plainview\sdk_mcc\form2\tests;

class TitleTest extends TestCase
{
	public function test_input_gets_title()
	{
		$text = $this->text();
		$text->title( 'This is a great title' );
		$this->assertStringContainsRegExp( '/\<input.*title="This is a great title"/s', $text );

		$text->title( 'This is a %s title', 'better' );
		$this->assertStringContainsRegExp( '/\<input.*title="This is a better title"/s', $text );

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

	/**
		@brief		Test setting the title unfiltered.
		@since		2018-02-07 12:19:16
	**/
	public function test_unfiltered_title()
	{
		$text = $this->text();
		$text->unfiltered_title( 'We should %s this', '<span>' );
		$this->assertStringContainsRegExp( '/We should <span> this/s', $text );
	}

	public function text()
	{
		return $this->form()->text( 'testtext' )
			->label( 'Test input' );
	}
}
