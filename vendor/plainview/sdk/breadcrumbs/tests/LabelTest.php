<?php

namespace plainview\sdk_mcc\breadcrumbs\tests;

class LabelTest extends TestCase
{
	public function test_clean()
	{
		$string = 'Testing a label';
		$bcs = $this->bcs();
		$bc = $bcs->breadcrumb( 'test' )
			->label( $string );
		$this->assertStringContains( $string, $bc );
	}

	public function test_escaped()
	{
		$string = 'Testing <h1>A label & something else.';
		$escaped = 'Testing &lt;h1&gt;A label &amp; something else.';
		$bcs = $this->bcs();
		$bc = $bcs->breadcrumb( 'test' )
			->label( $string );
		$this->assertStringDoesNotContain( $string, $bc );
		$this->assertStringContains( $escaped, $bc );
	}
}
