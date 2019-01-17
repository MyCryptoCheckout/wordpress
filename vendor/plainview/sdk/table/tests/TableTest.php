<?php

class TableTest extends \plainview\sdk_mcc\tests\TestCase
{
	public function test_sections()
	{
		$t = $this->table();
		$this->assertNotEquals( null, $t->caption() );
		$this->assertNotEquals( null, $t->head() );
		$this->assertNotEquals( null, $t->body() );
		$this->assertNotEquals( null, $t->foot() );
	}
	public function test_row_id()
	{
		$t = $this->table();

		// Without an ID
		$row = $t->head()->row();
		$this->assertEquals( null, $row->get_attribute( 'id' ) );

		// With an ID
		$id = 'test123';
		$row = $t->head()->row( $id );
		$this->assertEquals( $id, $row->get_attribute( 'id' ) );
	}

	public function test_cell_id()
	{
		$t = $this->table();
		$row = $t->body()->row();

		// Without an ID
		$cell = $row->td();
		$this->assertEquals( null, $cell->get_attribute( 'id' ) );

		// With an ID
		$id = 'test1234';
		$cell = $row->td( $id );
		$this->assertEquals( $id, $cell->get_attribute( 'id' ) );
	}

	public function test_count()
	{
		$t = $this->table();

		$this->assertEquals( 0, count( $t ) );
		$this->assertEquals( 0, count( $t->body() ) );
		// Create an anonymous row
		$this->assertEquals( 0, count( $t->body()->row() ) );

		// Create the row "first"
		$first = $t->body()->row( 'first' );
		$this->assertEquals( 2, count( $t ) );
		$this->assertEquals( 2, count( $t->body() ) );
		// Row has no cells
		$this->assertEquals( 0, count( $t->body()->row( 'first' ) ) );

		$first->td();
		// Row has one td cell.
		$this->assertEquals( 1, count( $t->body()->row( 'first' ) ) );
	}

	/**
		@brief		Test cell extraction.
		@since		2015-12-10 13:47:43
	**/
	public function test_cell_extraction()
	{
		$t = $this->table();
		$row = $t->body()->row();

		// Create a cell with an ID.
		$row->td( 'testid' );

		// Retrieve the same cell.
		$cell = $row->cell( 'testid' );
		$this->assertTrue( is_object( $cell ) );

		// And now retrieve a nonexistent cell. Should be false, not null.
		$value = $row->cell( 'bad_id' );
		$this->assertTrue( $value === false );
	}

	public function table()
	{
		return new \plainview\sdk_mcc\table\table;
	}
}
