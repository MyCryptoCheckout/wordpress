<?php

namespace plainview\sdk_mcc\form2\tests;

class SelectTest extends TestCase
{
	public function select()
	{
		return $this->form()->select( 'selecttest' )
			->label( 'Select' )
			->option( 'Select 1', 'sel1' )
			->option( 'Select 2', 'sel2' )
			->option( 'Select 3', 'sel3' )
			->value( 'sel2' );
	}

	public function select_multiple()
	{
		return $this->select()->multiple()->value( 'sel1', 'sel2' );
	}

	public function test_count()
	{
		$select = $this->select();
		$this->assertEquals( 3, count( $select ) );
	}

	public function test_count_with_optgroup()
	{
		$select = $this->select();
		// Add a simple optgroup
		$select->optgroup( 'testoptgroup' )
			->option( 'Optgroup option 1', 'v1' )
			->option( 'Optgroup option 2', 'v2' )
			->option( 'Optgroup option 3', 'v3' );
		// 7, because three options + three optgroupoptions + the optgroup itself!
		$this->assertEquals( 7, count( $select ) );
	}

	public function test_ids()
	{
		$sel = $this->select()->display_input();
		$this->assertStringContains( 'id="plainview_sdk_form2_inputs_select_selecttest"', $sel );
		$this->assertStringDoesNotContain( 'id="selecttest', $sel );
	}

	public function test_names()
	{
		$sel = $this->select()->display_input();
		$this->assertStringContains( 'name="selecttest"', $sel );
	}

	public function test_selected()
	{
		$sel = $this->select()->display_input();
		$this->assertStringContainsRegexp( '/\.*\<option.*\<option.*\"selected\".*Select 2.*\<option/s', $sel );
	}

	public function test_selected_multiple()
	{
		$sel = $this->select_multiple()->display_input();
		$this->assertStringContainsRegexp( '/\.*\<option.*\"selected\".*\<option.*\"selected\".*.*\<option/s', $sel );
	}

	public function test_name_multiple()
	{
		$sel = $this->select_multiple()->display_input();
		$this->assertStringContains( 'name="selecttest[]"', $sel );
	}
}
