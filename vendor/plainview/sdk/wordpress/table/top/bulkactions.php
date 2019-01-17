<?php

namespace plainview\sdk_mcc\wordpress\table\top;

use \plainview\sdk_mcc\collections\collection;

/**
	@brief		Bulk actions controller.

	@details

	Used to add bulk actions above the table.
	@since		20131015
**/
class bulkactions
{
	use \plainview\sdk_mcc\traits\method_chaining;

	public $bulk_actions_button;
	public $bulk_actions_input;
	public $checkboxes;
	public $form;
	public $table;

	public function __construct( $table )
	{
		$this->checkboxes = new collection;
		$this->table = $table;
	}

	public function __toString()
	{
		// If there aren't any options, don't bother displaying anything.
		if ( count( $this->bulk_actions_input->options ) < 2 )
			return '';

		return sprintf( '<div class="screen-reader-text">%s</div>%s%s',
			$this->bulk_actions_input->display_label(),
			$this->bulk_actions_input->display_input(),
			$this->bulk_actions_button->display_input()
		);
	}

	/**
		@brief		Add a bulk action to the select box of bulk actions.
		@param		string		$label		Label of new select option.
		@param		string		$value		The HTML value of the select option.
		@since		20131015
	**/
	public function add( $label, $value )
	{
		$this->bulk_actions_input->option( $label, $value );
		return $this->sort_options();
	}

	/**
		@brief		Create a checkbox column in the table header or the body.
		@details	If the $row is in the body section, the $id parameter must also be given.
		@param		row			$row		Row in the table. Automatically detects if the row is in the head or the body.
		@param		mixed		$id			The ID of this row. String or int.
		@return		mixed		Null if creating a checkbox in the header, else the checkbox input.
		@since		20131015
	**/
	public function cb( $row, $id = null )
	{
		$r = null;

		$section = get_class( $row->section );
		if ( $section == 'plainview\\sdk_mcc\\table\\head' )
		{
			// Create a temporary form in order to create a checkbox that is only used by javascript.
			$temp_form = clone( $this->form );
			// Create the temporary checkbox.
			$sa_text = __( 'Select All' );
			$checkbox = $temp_form->checkbox( 'cb_select_all_1' );
			$checkbox->label( $sa_text );
			$checkbox->title( $sa_text );
			// Hide the label
			$checkbox->label->css_class( 'screen-reader-text' );

			$text = sprintf( '%s%s', $checkbox->display_label(), $checkbox->display_input() );
			$row->td( 'check_column' )
				->css_class( 'manage-column check-column' )
				->text( $text );
		}

		if ( $section == 'plainview\\sdk_mcc\\table\\body' )
		{
			// Create the row checkbox.
			$cb = $this->form->checkbox( $id )
				->prefix( 'cb' );

			$text = $cb->display_input() . '<span class="screen-reader-text">' . $cb->display_label() . '</span>';
			$row->th( 'check_column_' . $row->id )->css_class( 'check-column' )->set_attribute( 'scope', 'row' )->text( $text );

			// Add the checkbox to a quick lookup table
			$this->checkboxes->append( $cb );
			$r = $cb;
		}

		return $r;
	}

	/**
		@brief		Set the form object to be used with the actions.
		@details	The form is cloned as to not interfere with the other inputs in the form.
		@since		20131015
	**/
	public function form( $form )
	{
		$form = clone( $form );

		$this->bulk_actions_button = $form->secondary_button( 'bulk_actions_apply' )
			->value( __( 'Apply' ) );
		$this->bulk_actions_input = $form->select( 'bulk_actions' )
			->label( __( 'Bulk actions' ) )
			->option( __( 'Bulk Actions' ), '' );

		// The default should always be first.
		$this->bulk_actions_input->option( '' )
			->sort_order( 25 );

		return $this->set_key( 'form', $form );
	}

	/**
		@brief		Get which action was selected.
		@since		20131015
	**/
	public function get_action()
	{
		return $this->bulk_actions_input->get_post_value();
	}

	/**
		@brief		Return an array of select row values.
		@details	The values are the $id parameter given to cb().
		@since		20131015
	**/
	public function get_rows()
	{
		if ( isset( $_POST[ 'cb' ] ) )
			return array_keys( $_POST[ 'cb' ] );
		else
			return [];
	}

	/**
		@brief		Was the Apply button pressed?
		@since		20131015
	**/
	public function pressed()
	{
		if ( ! $this->form->is_posting() )
			return false;
		$this->form->post();
		return $this->bulk_actions_button->pressed();
	}

	/**
		@brief		Sort the bulk options.
		@since		2016-01-12 22:19:52
	**/
	public function sort_options()
	{
		$this->bulk_actions_input
			->sort_inputs();
		return $this;
	}
}
