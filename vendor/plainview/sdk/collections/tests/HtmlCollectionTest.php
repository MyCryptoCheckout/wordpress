<?php

use \plainview\sdk_mcc\collections;

class HtmlCollectionTest
extends \plainview\sdk_mcc\tests\TestCase
{
	public function c()
	{
		$c = new collections\html();
		return $c;
	}

	/**
		@brief		Test HTML appends.
		@since		2015-11-06 17:38:18
	**/
	public function test_html_appends()
	{
		$c = $this->c();

		$c->append( '<p>This is the first line.</p>' );
		$c->append( 'This is the second line.' );

		$this->assertEquals( $c . '', "<p>This is the first line.</p>\n<p>This is the second line.</p>\n" );
	}

	/**
		@brief		Test the newline function.
		@since		2015-11-06 17:42:07
	**/
	public function test_newline()
	{
		$c = $this->c();

		$c->append( 'First' );
		$c->newline();
		$c->append( 'Second' );

		$this->assertEquals( $c . '', "<p>First</p>\n<p>Second</p>\n" );
	}

	/**
		@brief		Add plaintext strings.
		@since		2015-11-06 17:35:10
	**/
	public function test_plaintext_lines()
	{
		$c = $this->c();

		$c->append( 'First' );
		$c->append( 'Second' );
		$c->append( 'Third' );

		$this->assertEquals( $c . '', "<p>First<br />\nSecond<br />\nThird</p>\n" );
	}

	/**
		@brief		Test plaintext newlines.
		@since		2015-11-06 17:40:50
	**/
	public function test_plaintext_newlines()
	{
		$c = $this->c();

		$c->append( 'First' );
		$c->append( '' );
		$c->append( 'Second' );

		$this->assertEquals( $c . '', "<p>First</p>\n<p>Second</p>\n" );
	}
}
