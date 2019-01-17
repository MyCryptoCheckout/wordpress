<?php

use \plainview\sdk_mcc\collections\collection;

class CollectionTest
extends \plainview\sdk_mcc\tests\TestCase
{
	public function c()
	{
		$c = new collection;
		$c->set( 'C', '4' );
		$c->set( 'A', '5' );
		$c->set( 'B', '6' );
		$c->set( 'a', '9' );
		return $c;
	}

	/**
		@brief		Sort by the array values.
		@since		2014-01-23 22:58:21
	**/
	public function test_sort_by()
	{
		$c = $this->c();

		$c->sort_by( function( $value )
		{
			return $value;
		});

		$a = $c->to_array();

		reset( $a );
		$this->assertEquals( current( $a ), 4 );
		next( $a );
		$this->assertEquals( current( $a ), 5 );
		next( $a );
		$this->assertEquals( current( $a ), 6 );
		next( $a );
		$this->assertEquals( current( $a ), 9 );
	}

	/**
		@brief		Sort by the keys.
		@since		2014-01-23 22:58:21
	**/
	public function test_sort_by_key()
	{
		$c = $this->c();

		$c->sort_by_key();

		$a = $c->to_array();

		reset( $a );
		$this->assertEquals( current( $a ), 5 );
		next( $a );
		$this->assertEquals( current( $a ), 6 );
		next( $a );
		$this->assertEquals( current( $a ), 4 );
		next( $a );
		$this->assertEquals( current( $a ), 9 );
	}
}
