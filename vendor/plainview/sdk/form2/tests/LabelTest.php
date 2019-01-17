<?php

namespace plainview\sdk_mcc\form2\tests;

class LabelTest extends TestCase
{
	public function test_nice_label()
	{
		$text = $this->form()->text( 'text' )->label( 'Nice label' );
		$label = $text->display_label();
		$this->assertStringContains( '>Nice label<', $label );
		$this->assertStringContains( '<label', $label );

		$text = $this->form()->text( 'text' )->label( 'Hello %d 2 3', 1 );
		$label = $text->display_label();
		$this->assertStringContains( '1 2 3', $label );

		$text = $this->form()->text( 'text' )->unfiltered_label( 'Hello %s 2 3', 'x' );
		$label = $text->display_label();
		$this->assertStringContains( 'x 2 3', $label );
	}

	public function test_html_label()
	{
		$text = $this->form()->text( 'text' )->label( '<b>Bold</b>' );
		$label = $text->display_label();
		$this->assertStringContains( '<label', $label );
		$this->assertStringContains( '>&lt;b&gt;Bold&lt;/b&gt;<', $label );
	}

	/**
		@brief		Select options do not have any form of label.
	**/
	public function test_select_labels()
	{
		$select = $this->form()->select( 'LabelTest' );
		$select->label( 'Select label' )
			->option( 'Option label', 'optionlabel1' );
		$options = $select->display_input();
		$this->assertStringDoesNotContain( '</label>', $options );
		$this->assertStringContains( 'value="optionlabel1"', $options );
		$this->assertStringContains( 'Option label', $options );
	}
}
