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
				'tab' => 'autosettlement_edit',
				'autosettlement_id' => $index,
			] );
			$url = sprintf( '<a href="%s" title="%s">%s</a>',
				$url,
				__( 'Edit this autosettlement', 'mycryptocheckout' ),
				$autosettlements->get_types_as_options()[ $autosettlement->get_type() ]
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
								$message = sprintf( 'Success for %s: %s', $autosettlement->get_type(), $autosettlement->test() );
								$r .= $this->info_message_box()->_( $message );
							}
							catch( Exception $e )
							{
								$message = sprintf( 'Fail for %s: %s', $autosettlement->get_type(), $e->getMessage() );
								$r .= $this->error_message_box()->_( $message );
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

		$autosettlement_text = sprintf(
			// perhaps <a>we can ...you</a>
			__( "Read the full %sfiat autosettlement documentation%s to learn more about this feature.", 'mycryptocheckout' ),
			'<a href="https://mycryptocheckout.com/doc/autosettlements/" target="_blank">',
			'</a>'
		);

		$r .= wpautop( $autosettlement_text );

		$r .= $this->h2( __( 'Autosettlements', 'mycryptocheckout' ) );

		$r .= $form->open_tag();
		$r .= $table;
		$r .= $form->close_tag();
		$r .= $form->open_tag();
		$r .= $form->display_form_table();
		$r .= $form->close_tag();

		echo $r;
	}

	/**
		@brief		Edit this autosettlement setting.
		@since		2019-02-21 22:47:10
	**/
	public function autosettlement_edit( $id )
	{
		$autosettlements = $this->autosettlements();
		if ( ! $autosettlements->has( $id ) )
		{
			echo 'Invalid ID!';
			return;
		}
		$autosettlement = $autosettlements->get( $id );

		$form = $this->form();
		$form->id( 'autosettlement_edit' );
		$r = '';

		switch( $autosettlement->get_type() )
		{
			case 'binance':
				$m_binance = $form->markup( 'm_binance' )
					->value( 'Your Binance balance will be checked every few minutes for an hour after a payment is detected for selected coins. If you have more than the minimum trade size, it will be market sold into the autosettlement currency of your choice.' );

				$form->markup( 'm_binance_api' )
					->value( 'See how to <a href="https://mycryptocheckout.com/doc/binance/">get Binance API keys</a> and set permissions.' );

				$binance_api_key = $form->text( 'binance_api_key' )
					->description( __( 'The API key of your Binance account.', 'mycryptocheckout' ) )
					// Input label
					->label( __( 'Binance API key', 'mycryptocheckout' ) )
					->size( 32 )
					->maxlength( 64 )
					->trim()
					->value( $autosettlement->get( 'binance_api_key' ) );
				$binance_api_secret = $form->text( 'binance_api_secret' )
					->description( __( 'The secret text associated to this API key.', 'mycryptocheckout' ) )
					// Input label
					->label( __( 'Binance secret', 'mycryptocheckout' ) )
					->size( 32 )
					->maxlength( 64 )
					->trim()
					->value( $autosettlement->get( 'binance_api_secret' ) );
				$binance_settlement_currency = $form->select( 'binance_settlement_currency' )
					->description( __( 'The currency you wish to settle to.', 'mycryptocheckout' ) )
					->label( __( 'Autosettlement currency', 'mycryptocheckout' ) )
					->opt( 'BUSD', 'BUSD - Binance USD Coin' )
					->opt( 'USDC', 'USDC - USD Coin' )
					->opt( 'USDT', 'USDT - Tether' )
					->opt( 'TUSD', 'TUSD - TrueUSD' )
					->value( $autosettlement->get( 'binance_settlement_currency', 'USDT' ) );
				break;
			case 'bittrex':
				$m_bittrex = $form->markup( 'm_bittrex' )
					->value( 'Your Bittrex balance will be checked every few minutes for an hour after a payment is detected for selected coins. If you have more than the minimum trade size, it will be market sold into the autosettlement currency of your choice.' );

				$form->markup( 'm_bittrex_api' )
					->value( 'See how to <a href="https://mycryptocheckout.com/doc/autosettlements/bittrex/">get Bittrex API keys</a> and set permissions.' );

				$bittrex_api_key = $form->text( 'bittrex_api_key' )
					->description( __( 'The limited API key of your Bittrex account.', 'mycryptocheckout' ) )
					// Input label
					->label( __( 'Bittrex API key', 'mycryptocheckout' ) )
					->size( 32 )
					->maxlength( 32 )
					->trim()
					->value( $autosettlement->get( 'bittrex_api_key' ) );
				$bittrex_api_secret = $form->text( 'bittrex_api_secret' )
					->description( __( 'The secret text associated to this API key.', 'mycryptocheckout' ) )
					// Input label
					->label( __( 'Bittrex secret', 'mycryptocheckout' ) )
					->size( 32 )
					->trim()
					->value( $autosettlement->get( 'bittrex_api_secret' ) );
				$bittrex_settlement_currency = $form->select( 'bittrex_settlement_currency' )
					->description( __( 'The currency you wish to settle to.', 'mycryptocheckout' ) )
					->label( __( 'Autosettlement currency', 'mycryptocheckout' ) )
					->opt( 'USD', 'USD - US Dollars' )
					->opt( 'USDT', 'USDT - USD Tether' )
					->value( $autosettlement->get( 'bittrex_settlement_currency', 'USD' ) );
			break;
		}

		$autosettlement_label = $form->text( 'wallet_label' )
			->description( __( 'Describe this autosettlement to yourself.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Label', 'mycryptocheckout' ) )
			->size( 32 )
			->stripslashes()
			->trim()
			->value( $autosettlement->get_label() );

		// Which currencies to apply this autosettlement on.
		$fs = $form->fieldset( 'fs_currencies' );
		// Fieldset legend
		$fs->legend->label( __( 'Currencies', 'mycryptocheckout' ) );

		$currencies_input = $fs->select( 'currencies' )
			->description( __( 'Select the currencies to be autosettled. If no currencies are selected, these settings will be applied to all of them. Hold the ctrl or shift key to select multiple currencies.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Currencies to autosettle', 'mycryptocheckout' ) )
			->multiple()
			->size( 20 )
			->value( $autosettlement->get_currencies() );
		$this->currencies()->add_to_select_options( $currencies_input );

		if ( $this->is_network && is_super_admin() )
			$autosettlement->add_network_fields( $form );

		$save = $form->primary_button( 'save' )
			->value( __( 'Save settings', 'mycryptocheckout' ) );

		if ( $form->is_posting() )
		{
			$form->post();
			$form->use_post_values();

			$reshow = false;

			if ( $save->pressed() )
			{
				try
				{
					switch( $autosettlement->get_type() )
					{
						case 'binance':
							$value = $binance_api_key->get_filtered_post_value();
							$autosettlement->set( 'binance_api_key', $value );
							$value = $binance_api_secret->get_filtered_post_value();
							$autosettlement->set( 'binance_api_secret', $value );
							$value = $binance_settlement_currency->get_filtered_post_value();
							$autosettlement->set( 'binance_settlement_currency', $value );
							break;
						case 'bittrex':
							$value = $bittrex_api_key->get_filtered_post_value();
							$autosettlement->set( 'bittrex_api_key', $value );
							$value = $bittrex_api_secret->get_filtered_post_value();
							$autosettlement->set( 'bittrex_api_secret', $value );
							$value = $bittrex_settlement_currency->get_filtered_post_value();
							$autosettlement->set( 'bittrex_settlement_currency', $value );
							break;
					}

					$autosettlement->set_label( $autosettlement_label->get_filtered_post_value() );

					$autosettlement->set_currencies( $currencies_input->get_post_value() );

					$autosettlement->maybe_parse_network_form_post( $form );

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
				echo $this->$function( $id );
				return;
			}
		}

		$r .= $form->open_tag();
		$r .= $form->display_form_table();
		$r .= $form->close_tag();

		echo $r;
	}

	/**
		@brief		Return an array of the keys to copy to the payment.
		@since		2019-04-11 20:57:56
	**/
	public static function autosettlement_keys_to_payment( $type )
	{
		switch( $type )
		{
			case 'binance':
				$r = [ 'binance_api_key', 'binance_api_secret', 'binance_settlement_currency' ];
			break;
			case 'bittrex':
				$r = [ 'bittrex_api_key', 'bittrex_api_secret', 'bittrex_settlement_currency' ];
			break;
			default:
				$r = [];
		}
		return $r;
	}
}
