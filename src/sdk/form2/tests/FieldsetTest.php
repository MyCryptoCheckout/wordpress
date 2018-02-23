<?php

namespace plainview\sdk_mcc\form2\tests;

class FieldsetTest extends TestCase
{
	public function fs()
	{
		return $this->form()->fieldset( 'fieldsettest' )
			->label( 'Fieldset test' );
	}

	public function test_legend()
	{
		$fs = $this->fs();
		$this->assertStringContains( '<legend>Fieldset test</legend>', $fs );
	}

	/**
		@brief		Test legend with sprintf.
		@since		2018-02-07 12:35:36
	**/
	public function test_legend_sprintf()
	{
		$fs = $this->fs();
		$fs->label( 'Hello %s', 'legend' );
		$this->assertStringContains( '<legend>Hello legend</legend>', $fs );
	}
}
