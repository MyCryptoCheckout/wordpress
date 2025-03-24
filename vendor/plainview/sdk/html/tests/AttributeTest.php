<?php

namespace plainview\sdk_mcc\html\tests;

class AttributeTest extends TestCase
{
	public function test_valid_key()
	{
		$this->div()->set_attribute( 'good_key', 'ignore' );
		$this->assertTrue( true );
	}

	/**
		@brief	Test setting an invalid key.
		@since		2017-10-02 00:46:43
	**/
	public function test_invalid_key()
	{
		$this->expectException( \plainview\sdk_mcc\html\exceptions\InvalidKeyException::class );
		$this->div()->set_attribute( 'bad key', 'ignore' );
	}

	public function test_set_attribute()
	{
		$string = "string 1234 smörgåsbord ''' &more html;";
		$div = $this->div()->set_attribute( 'safe', $string );
		$this->assertEquals( $div->get_attribute( 'safe' ), $string );
	}

	public function test_append()
	{
		$div = $this->div()->set_attribute( 'test', 'first_attribute' )
			->append_attribute( 'test', 'second attribute' );
		$this->assertEquals( $div->get_attribute( 'test' ), 'first_attributesecond attribute' );
	}

	public function test_class_append()
	{
		$div = $this->div()->css_class( 'first one' )
			->css_class( 'second one' );
		$this->assertEquals( $div->get_attribute( 'class' ), 'first one second one' );
	}

	public function test_clear()
	{
		$div = $this->div()->set_attribute( 'test', 'new value' );
		$this->assertEquals( $div->get_attribute( 'test' ), 'new value' );

		$div->clear_attribute( 'test' );
		$this->assertEquals( $div->get_attribute( 'test' ), null );
	}

	/**
		@brief		Test attributes with no value.
		@group		now
		@since		2023-12-10 21:13:56
	**/
	public function test_attributes_with_no_value()
	{
		$div = $this->div();

		// Make sure there is no required there at all.
		$strpos = strpos( $div, 'required' );
		$this->assertFalse( $strpos );

		$div->required();

		// Check that is has no value.
		$strpos = strpos( $div, 'required=' );
		$this->assertFalse( $strpos );

		// Check that it exists.
		$strpos = strpos( $div, 'required' );
		$this->assertEquals( '5', $strpos );
	}
}
