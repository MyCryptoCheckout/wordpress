<?php

class TreeTest extends \plainview\sdk_mcc\tests\TestCase
{
	public function tree()
	{
		$tree = new \plainview\sdk_mcc\tree\tree;
		return $tree;
	}

	public function add_level_one( $tree )
	{
		$one = 'one';
		$two = 'two';
		$three = 'three';

		$tree->add( 1, $one );
		$tree->add( 2, $two );
		$tree->add( 3, $three );

		return $tree;
	}

	public function add_level_two( $tree )
	{
		$one_one = 'one';
		$one_two = 'two';
		$three_one = 'three';

		$tree->add( '1_2', $one_two, '1' );
		$tree->add( '1_1', $one_one, '1' );
		$tree->add( '3_1', $three_one, '3' );

		return $tree;
	}

	public function test_depths()
	{
		$tree = $this->tree();

		// Add this as an orphan to test correct depth.
		$tree->add( '1_2_3', true, '1_2' );

		$tree = $this->add_level_two( $tree );
		$tree = $this->add_level_one( $tree );

		$this->assertEquals( 1, $tree->node( '1' )->get_depth() );
		$this->assertEquals( 2, $tree->node( '1_2' )->get_depth() );
		$this->assertEquals( 3, $tree->node( '1_2_3' )->get_depth() );
	}

	public function test_get_node()
	{
		$tree = $this->tree();
		$tree = $this->add_level_one( $tree );
		$tree = $this->add_level_two( $tree );
		$this->assertTrue( $tree->node( '1' ) !== null );
		$this->assertTrue( $tree->node( '1_2' ) !== null );
		$this->assertNull( $tree->node( '2_1' ) );
	}

	public function test_one_level_count()
	{
		$tree = $this->tree();
		$tree = $this->add_level_one( $tree );
		$this->assertEquals( 3, count( $tree ) );
	}

	/**
		@brief		Test the sorting.
		@since		20131208
	**/
	public function test_sorting()
	{
		$tree = $this->tree();
		$tree = $this->add_level_one( $tree );
		$tree = $this->add_level_two( $tree );
		$this->assertStringContainsRegexp( '/1_2.*1_1/s', $tree );
		$tree->sort();
		$this->assertStringDoesNotContainRegexp( '/1_2.*1_1/s', $tree );
		$this->assertStringContainsRegexp( '/1_1.*1_2/s', $tree );
	}

	public function test_two_level_count()
	{
		$tree = $this->tree();
		$tree = $this->add_level_one( $tree );
		$tree = $this->add_level_two( $tree );
		$this->assertEquals( 6, count( $tree ) );
	}

	/**
		@brief		Added level two before one should still give us 6, because of no orphans.
		@since		20131208
	**/
	public function test_two_level_orphan_count()
	{
		$tree = $this->tree();
		$tree = $this->add_level_two( $tree );

		// Level two will leave us with three orphans.
		$this->assertEquals( 3, count( $tree->orphans ) );

		// And no nodes
		$this->assertEquals( 0, count( $tree ) );

		$tree = $this->add_level_one( $tree );

		// Fully populated with two levels.
		$this->assertEquals( 6, count( $tree ) );

		// No more orphans.
		$this->assertEquals( 0, count( $tree->orphans ) );
	}
}
