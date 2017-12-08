<?php

namespace plainview\sdk_mcc\form2\tests;

class Sort_Order_Test extends TestCase
{
	/**
		@brief		Create a form with some sortable inputs.
		@since		2015-12-25 17:07:52
	**/
	public function create_sortable_inputs()
	{
		// Create some inputs.
		$form = $this->form();

		$form->text( 'first' )
			->label( 'First' )
			->sort_order( 50 );

		$form->text( 'second' )
			->label( 'Second' )
			->sort_order( 10 );

		$form->text( 'almost_second' )
			->label( 'Almost second' )
			->sort_order( 10 );

		return $form;
	}

	/**
		@brief		Test the sorting method.
		@since		2015-12-25 17:17:54
	**/
	public function test_sorted_order()
	{
		$form = $this->create_sortable_inputs();

		$form->sort_inputs();

		$inputs = array_keys( $form->inputs );

		$this->AssertEquals( array_search( 'first', $inputs ), 2 );		// Order 50
		$this->AssertEquals( array_search( 'second', $inputs ), 1 );	// Order 10, but S > A
		$this->AssertEquals( array_search( 'almost_second', $inputs ), 0 );
	}

	/**
		@brief		Test the sorting of select options.
		@since		2016-01-12 23:50:04
	**/
	public function test_sorted_select()
	{
		$form = $this->form();
		$select = $form->select( 'sort_test' )
			->option( 'Second', 'second' )
			->option( 'First', 'first' );

		// Check that second is first, as added.
		$option_keys = array_keys( $select->options );
		$this->AssertEquals( array_search( 'first', $option_keys ), 1 );

		$select->sort_inputs();

		// Check that first is first, as added.
		$option_keys = array_keys( $select->options );
		$this->AssertEquals( array_search( 'first', $option_keys ), 0 );

		// Modify second with a higher sort order.
		$select->option( 'first' )
			->sort_order( 75 );

		$select->sort_inputs();

		// Check that first is now last.
		$option_keys = array_keys( $select->options );
		$this->AssertEquals( array_search( 'first', $option_keys ), 1 );

	}

	/**
		@brief		Create a form without sorting the inputs.
		@since		2015-12-25 17:10:56
	**/
	public function test_unsorted_order()
	{
		$form = $this->create_sortable_inputs();

		// Check that the inputs are in the order we created them.
		$inputs = array_keys( $form->inputs );

		$this->AssertEquals( array_search( 'first', $inputs ), 0 );
		$this->AssertEquals( array_search( 'second', $inputs ), 1 );
		$this->AssertEquals( array_search( 'almost_second', $inputs ), 2 );
	}

}
