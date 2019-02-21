<?php

namespace mycryptocheckout;

use Exception;

trait autosettlement_trait
{
	/**
		@brief		Return the autosettlements collection.
		@since		2019-02-21 19:29:10
	**/
	public function autosettlements()
	{
		if ( isset( $this->__autosettlements ) )
			return $this->__autosettlements;

		$this->__autosettlements = autosettlements\Autosettlements::load();
		return $this->__autosettlements;
	}

	/**
		@brief		Administer the autosettlement settings.
		@since		2019-02-21 18:58:44
	**/
	public function autosettlement_admin()
	{
		$form = $this->form();
		$form->id( 'autosettlements' );
		$r = '';

		$table = $this->table();
		$table->css_class( 'autosettlements' );

		$table->bulk_actions()
			->form( $form )
			// Bulk action for autosettlement settings
			->add( __( 'Delete', 'mycryptocheckout' ), 'delete' )
			// Bulk action for autosettlement settings
			->add( __( 'Disable', 'mycryptocheckout' ), 'disable' )
			// Bulk action for autosettlement settings
			->add( __( 'Enable', 'mycryptocheckout' ), 'enable' )
			// Bulk action for autosettlement settings
			->add( __( 'Test', 'mycryptocheckout' ), 'test' )
			;

		// Assemble the autosettlements
		$row = $table->head()->row();
		$table->bulk_actions()->cb( $row );
		// Table column name
		$row->th( 'type' )->text( __( 'Type', 'mycryptocheckout' ) );
		// Table column name
		$row->th( 'details' )->text( __( 'Details', 'mycryptocheckout' ) );

		$autosettlements = $this->autosettlements();

		foreach( $autosettlements as $index => $autosettlement )
		{
			$row = $table->body()->row();
			$row->data( 'index', $index );
			$table->bulk_actions()->cb( $row, $index );

			// Address
			$url = add_query_arg( [
				'tab' => 'edit_autosettlement',
				'autosettlement_id' => $index,
			] );
			$url = sprintf( '<a href="%s" title="%s">%s</a>',
				$url,
				__( 'Edit this autosettlement', 'mycryptocheckout' ),
				$autosettlement->get_type()
			);
			$row->td( 'type' )->text( $url );

			// Details
			$details = $autosettlement->get_details();
			$details = implode( "\n", $details );
			$row->td( 'details' )->text( wpautop( $details ) );
		}

		$fs = $form->fieldset( 'fs_add_new' );
		// Fieldset legend
		$fs->legend->label( __( 'Add new autosettlement', 'mycryptocheckout' ) );

		$autosettlement_type = $fs->select( 'autosettlement_type' )
			->css_class( 'autosettlement_type' )
			->description( __( 'Which type of autosettlement do you wish to add?', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Type', 'mycryptocheckout' ) )
			->opts( $autosettlements->get_types_as_options() );

		$save = $form->primary_button( 'save' )
			->value( __( 'Save settings', 'mycryptocheckout' ) );

		if ( $form->is_posting() )
		{
			$form->post();
			$form->use_post_values();

			$reshow = false;

			if ( $table->bulk_actions()->pressed() )
			{
				$ids = $table->bulk_actions()->get_rows();
				switch ( $table->bulk_actions()->get_action() )
				{
					case 'delete':
						foreach( $ids as $id )
							$autosettlements->forget( $id );
						$autosettlements->save();
						$r .= $this->info_message_box()->_( __( 'The selected wallets have been deleted.', 'mycryptocheckout' ) );
					break;
					case 'disable':
						foreach( $ids as $id )
						{
							$autosettlement = $autosettlements->get( $id );
							$autosettlement->set_enabled( false );
						}
						$autosettlements->save();
						$r .= $this->info_message_box()->_( __( 'The selected wallets have been disabled.', 'mycryptocheckout' ) );
					break;
					case 'enable':
						foreach( $ids as $id )
						{
							$autosettlement = $autosettlements->get( $id );
							$autosettlement->set_enabled( true );
						}
						$autosettlements->save();
						$r .= $this->info_message_box()->_( __( 'The selected wallets have been enabled.', 'mycryptocheckout' ) );
					break;
					case 'test':
						foreach( $ids as $id )
						{
							$autosettlement = $autosettlements->get( $id );
							try
							{
								$r .= $this->info_message_box()->_( $autosettlement->test() );
							}
							catch( Exception $e )
							{
								$r .= $this->error_message_box()->_( $e->getMessage() );
							}
						}
					break;
				}
				$reshow = true;
			}

			if ( $save->pressed() )
			{
				try
				{
					$autosettlement = $autosettlements->new_autosettlement();
					$autosettlement->set_type( $autosettlement_type->get_filtered_post_value() );

					$index = $autosettlements->add( $autosettlement );
					$autosettlements->save();

					$r .= $this->info_message_box()->_( __( 'Settings saved!', 'mycryptocheckout' ) );
					$reshow = true;
				}
				catch ( Exception $e )
				{
					$r .= $this->error_message_box()->_( $e->getMessage() );
				}
			}

			if ( $reshow )
			{
				echo $r;
				$_POST = [];
				$function = __FUNCTION__;
				echo $this->$function();
				return;
			}
		}

		$r .= wpautop( __( 'The table below shows the autosettlements that have been set up. To edit an autosettlement, click the type.', 'mycryptocheckout' ) );

		$r .= $this->h2( __( 'Autosettlements', 'mycryptocheckout' ) );

		$r .= $form->open_tag();
		$r .= $table;
		$r .= $form->close_tag();
		$r .= $form->open_tag();
		$r .= $form->display_form_table();
		$r .= $form->close_tag();

		echo $r;
	}
}
