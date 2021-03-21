<?php

/**
	@brief		The Wordpress group.
	@group		wordpress
	@since		2021-02-05 16:24:20
**/
class WordpressTest
	extends \plainview\sdk_mcc\tests\TestCase
{
	/**
		@brief		Test the actions.
		@since		2021-02-05 16:24:32
	**/
	public function test_actions()
	{
		$action = new \plainview\sdk_mcc\wordpress\actions\TestAction();

		$this->assertEquals( $action->get_name(), 'abcTestActionxyz' );

		$orp = microtime();
		$action->set_prefix_override( $orp );
		$this->assertEquals( $action->get_name(), $orp . 'TestActionxyz' );

		$ors = microtime();
		$action->set_suffix_override( $ors );
		$this->assertEquals( $action->get_name(), $orp . 'TestAction' . $ors );

	}
}
