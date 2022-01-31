<?php

namespace plainview\sdk_mcc\html\tests;

/**
	@brief		Test the data function.
	@since		2015-11-29 11:37:13
**/
class DataTest extends TestCase
{
	/**
		@brief		Set a data value with an invalid key.
		@since		2015-11-29 11:42:54
	**/
	public function test_bad_set()
	{
		$key = 'bad key';
		$this->expectException( \plainview\sdk_mcc\html\exceptions\InvalidKeyException::class );
		$div = $this->div()->data( $key, 'ignore' );
	}

	/**
		@brief		Set a data value with a valid key.
		@since		2015-11-29 11:42:42
	**/
	public function test_good_set()
	{
		$key = 'good_key';
		$value = 'good_value';

		$div = $this->div()->data( $key, $value );

		$this->assertEquals( $div->data( $key ), $value );
	}
}
