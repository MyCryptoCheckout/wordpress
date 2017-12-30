<?php

namespace mycryptocheckout;

use \Exception;

/**
	@brief		Handles admin things such as settings and currencies.
	@since		2017-12-09 07:05:04
**/
trait admin_trait
{
	/**
		@brief		Do some activation.
		@since		2017-12-09 07:12:19
	**/
	public function activate()
	{
		wp_schedule_event( time(), 'hourly', 'mycryptocheckout_hourly' );
		// We need to run this as soon as the plugin is active.
		wp_schedule_single_event( time(), 'mycryptocheckout_hourly' );
	}

	/**
		@brief		Admin the account.
		@since		2017-12-11 14:20:17
	**/
	public function admin_account()
	{
		$form = $this->form();
		$form->id( 'broadcast_settings' );
		$r = '';

		if ( isset( $_POST[ 'retrieve_account' ] ) )
		{
			$result = $this->mycryptocheckout_retrieve_account();
			if ( $result )
				$r .= $this->info_message_box()->_( __( 'Account data refreshed!', 'mycryptocheckout' ) );
			else
				$r .= $this->error_message_box()->_( __( 'Error refreshing your account data. Please enable debug mode to find the error.', 'mycryptocheckout' ) );
		}

		$account = $this->api()->account();

		if ( ! $account->is_valid() )
			$r .= $this->admin_account_invalid();
		else
			$r .= $this->admin_account_valid( $account );

		$save = $form->secondary_button( 'retrieve_account' )
			->value( __( 'Refresh your account data', 'mycryptocheckout' ) );

		if ( $form->is_posting() )
		{
			$form->post();
			$form->use_post_values();
		}

		$r .= $form->open_tag();
		$r .= $form->display_form_table();
		$r .= $form->close_tag();

		echo $r;
	}

	/**
		@brief		Show the invalid account text.
		@since		2017-12-12 11:07:42
	**/
	public function admin_account_invalid()
	{
		$r = '';
		$r .= wpautop( __( 'It appears as if MyCrytpoCheckout was unable to retrieve your account data from the MyCryptoCheckout server.', 'mycryptocheckout' ) );
		$r .= wpautop( __( 'Use the button below to try and retrieve your account data again.', 'mycryptocheckout' ) );
		return $r;
	}

	/**
		@brief		Show the valid account text.
		@since		2017-12-12 11:07:42
	**/
	public function admin_account_valid( $account )
	{
		$r = '';

		try
		{
			$this->api()->account()->is_available_for_payment();
		}
		catch ( Exception $e )
		{
			$message = sprintf( '%s: %s',
				__( 'Payments using MyCryptoCheckout are currently not available', 'woocommerce' ),
				$e->getMessage()
			);
			$r .= $this->error_message_box()->_( $message );
		}

		$table = $this->table();
		$table->caption()->text( __( 'Information about your account on mycryptocheckout.com', 'mycryptocheckout' ) );

		$row = $table->head()->row()->hidden();
		// Table column name
		$row->th( 'key' )->text( __( 'Key', 'mycryptocheckout' ) );
		// Table column name
		$row->td( 'details' )->text( __( 'Details', 'mycryptocheckout' ) );

		if ( $this->debugging() )
		{
			$row = $table->head()->row();
			// Table column name
			$row->th( 'key' )->text( __( 'API key', 'mycryptocheckout' ) );
			// Table column name
			$row->td( 'details' )->text( $account->get_domain_key() );

			$row = $table->head()->row();
			// Table column name
			$row->th( 'key' )->text( __( 'Server name', 'mycryptocheckout' ) );
			// Table column name
			$row->td( 'details' )->text( $this->get_server_name() );
		}

		$row = $table->head()->row();
		// Table column name
		$row->th( 'key' )->text( __( 'Payments remaining this month', 'mycryptocheckout' ) );
		// Table column name
		$row->td( 'details' )->text( $account->get_payments_left() );

		$row = $table->head()->row();
		// Table column name
		$row->th( 'key' )->text( __( 'Payments processed', 'mycryptocheckout' ) );
		// Table column name
		$row->td( 'details' )->text( $account->get_payments_used() );

		$row = $table->head()->row();
		// Table column name
		$row->th( 'key' )->text( __( 'Physical currency exchange rates updated', 'mycryptocheckout' ) );
		// Table column name
		$time = $account->data->physical_exchange_rates->timestamp;
		$text = sprintf( '<span title="%s">%s</span>',
			$this->local_datetime( $time ),
			human_time_diff( $time )
		);
		$row->td( 'details' )->text( $text );

		$row = $table->head()->row();
		// Table column name
		$row->th( 'key' )->text( __( 'Cryptocurrency exchange rates updated', 'mycryptocheckout' ) );
		// Table column name
		$time = $account->data->virtual_exchange_rates->timestamp;
		$text = sprintf( '<span title="%s">%s</span>',
			$this->local_datetime( $time ),
			human_time_diff( $time )
		);
		$row->td( 'details' )->text( $text );

		$expiration = $this->api()->account()->get_license_expiration();
		if ( $expiration !== false )
		{
			$row = $table->head()->row();
			// Table column name
			$row->th( 'key' )->text( __( 'License expiration', 'mycryptocheckout' ) );
			// Table column name
			$value = $this->local_date( $expiration );
			$row->td( 'details' )->text( $value );
		}

		$row = $table->head()->row();
		// Table column name
		if ( ! $expiration )
			$text =  __( 'Purchase a license for unlimited payments', 'mycryptocheckout' );
		else
			$text =  __( 'Renew your license', 'mycryptocheckout' );
		$row->th( 'key' )->text( $text );
		// Table column name
		$url = $this->api()->get_purchase_url();
		$url = sprintf( '<a href="%s">%s</a>', $url, $url );
		$row->td( 'details' )->text( $url );

		$r .= $table;

		return $r;
	}

	/**
		@brief		Admin the currencies.
		@since		2017-12-09 07:06:56
	**/
	public function admin_currencies()
	{
		$form = $this->form();
		$form->id( 'broadcast_settings' );
		$r = '';

		$table = $this->table();

		$table->bulk_actions()
			->form( $form )
			// Bulk action for wallets
			->add( __( 'Delete', 'mycryptocheckout' ), 'delete' )
			// Bulk action for wallets
			->add( __( 'Disable', 'mycryptocheckout' ), 'disable' )
			// Bulk action for wallets
			->add( __( 'Enable', 'mycryptocheckout' ), 'enable' );

		// Assemble the current wallets into the table.
		$row = $table->head()->row();
		$table->bulk_actions()->cb( $row );
		// Table column name
		$row->th( 'currency' )->text( __( 'Currency', 'mycryptocheckout' ) );
		// Table column name
		$row->th( 'wallet' )->text( __( 'Wallet', 'mycryptocheckout' ) );
		// Table column name
		$row->th( 'details' )->text( __( 'Details', 'mycryptocheckout' ) );

		$wallets = $this->wallets();

		foreach( $wallets as $index => $wallet )
		{
			$row = $table->body()->row();
			$table->bulk_actions()->cb( $row, $index );
			$currency = $this->currencies()->get( $wallet->get_currency_id() );
			$currency_text = sprintf( '%s %s', $currency->get_name(), $currency->get_id() );
			$row->td( 'currency' )->text( $currency_text );

			// Address
			$url = add_query_arg( [
				'tab' => 'edit_wallet',
				'wallet_id' => $index,
			] );
			$url = sprintf( '<a href="%s" title="%s">%s</a>',
				$url,
				__( 'Edit this currency', 'mycryptocheckout' ),
				$wallet->get_address()
			);
			$row->td( 'wallet' )->text( $url );

			// Details
			$details = $wallet->get_details();
			$details = implode( "\n", $details );
			$row->td( 'details' )->text( wpautop( $details ) );
		}

		$fs = $form->fieldset( 'fs_add_new' );
		// Fieldset legend
		$fs->legend->label( __( 'Add new currency / wallet', 'mycryptocheckout' ) );

		$wallet_currency = $fs->select( 'currency' )
			->description( __( 'Which currency shall the new wallet belong to?', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Currency', 'mycryptocheckout' ) )
			->options( $this->currencies()->as_options() );

		$wallet_address = $fs->text( 'wallet_address' )
			->description( __( 'The address of your wallet to which you want to receive funds.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Address', 'mycryptocheckout' ) )
			->required()
			->size( 64, 256 )
			->trim();

		if ( $this->is_network )
			$wallet_on_network = $fs->checkbox( 'wallet_on_network' )
				->checked( true )
				->description( __( 'Do you want the wallet to be available on the whole network?', 'mycryptocheckout' ) )
				// Input label
				->label( __( 'Network wallet', 'mycryptocheckout' ) );

		$save = $form->primary_button( 'save' )
			->value( __( 'Save settings', 'mycryptocheckout' ) );

		if ( $form->is_posting() )
		{
			$form->post();
			$form->use_post_values();

			$reshow = false;

			if ( $table->bulk_actions()->pressed() )
			{
				switch ( $table->bulk_actions()->get_action() )
				{
					case 'delete':
						$ids = $table->bulk_actions()->get_rows();
						foreach( $ids as $id )
							$wallets->forget( $id );
						$wallets->save();
						$r .= $this->info_message_box()->_( __( 'The selected wallets have been deleted.', 'mycryptocheckout' ) );
					break;
					case 'disable':
						$ids = $table->bulk_actions()->get_rows();
						foreach( $ids as $id )
						{
							$wallet = $wallets->get( $id );
							$wallet->set_enabled( false );
						}
						$wallets->save();
						$r .= $this->info_message_box()->_( __( 'The selected wallets have been disabled.', 'mycryptocheckout' ) );
					break;
					case 'enable':
						$ids = $table->bulk_actions()->get_rows();
						foreach( $ids as $id )
						{
							$wallet = $wallets->get( $id );
							$wallet->set_enabled( true );
						}
						$wallets->save();
						$r .= $this->info_message_box()->_( __( 'The selected wallets have been disabled.', 'mycryptocheckout' ) );
					break;
				}
				$reshow = true;
			}

			if ( $save->pressed() )
			{
				try
				{
					$wallet = $wallets->new_wallet();
					$wallet->address = $wallet_address->get_filtered_post_value();
					if ( $this->is_network )
						$wallet->network = $wallet_on_network->is_checked();

					$chosen_currency = $wallet_currency->get_filtered_post_value();
					$currency = $this->currencies()->get( $chosen_currency );
					$currency->validate_address( $wallet->address );

					$wallet->currency_id = $chosen_currency;

					$wallets->add( $wallet );
					$wallets->save();
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

		$r .= wpautop( __( 'The table below shows the currencies and wallets you have set up in the plugin. To edit a wallet, click the address.', 'mycryptocheckout' ) );

		$r .= $form->open_tag();
		$r .= $table;
		$r .= $form->close_tag();
		$r .= $form->open_tag();
		$r .= $form->display_form_table();
		$r .= $form->close_tag();

		echo $r;
	}

	/**
		@brief		Edit this wallet.
		@since		2017-12-09 20:44:32
	**/
	public function admin_edit_wallet( $wallet_id )
	{
		$wallets = $this->wallets();
		if ( ! $wallets->has( $wallet_id ) )
		{
			echo 'Invalid wallet ID!';
			return;
		}
		$wallet = $wallets->get( $wallet_id );

		$currencies = $this->currencies();
		$currency = $currencies->get( $wallet->get_currency_id() );
		$form = $this->form();
		$form->id( 'broadcast_settings' );
		$form->css_class( 'plainview_form_auto_tabs' );
		$r = '';

		$wallet_address = $form->text( 'wallet_address' )
			->description( __( 'The address of your wallet to which you want to receive funds.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Address', 'mycryptocheckout' ) )
			->required()
			->size( $currency->get_address_length(), $currency->get_address_length() )
			->trim()
			->value( $wallet->get_address() );

		$wallet_enabled = $form->checkbox( 'wallet_enabled' )
			->checked( $wallet->enabled )
			->description( __( 'Is this wallet enabled and ready to receive funds?', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Enabled', 'mycryptocheckout' ) );

		$confirmations = $form->number( 'confirmations' )
			->description( __( 'How many confirmations needed to regard orders as paid. 1 is the default. More confirmations take longer.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Confirmations', 'mycryptocheckout' ) )
			->min( 1, 100 )
			->value( $wallet->confirmations );

		if ( $this->is_network )
			$wallet_on_network = $form->checkbox( 'wallet_on_network' )
				->checked( $wallet->network )
				->description( __( 'Do you want the wallet to be available on the whole network?', 'mycryptocheckout' ) )
				// Input label
				->label( __( 'Network wallet', 'mycryptocheckout' ) );

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
					$wallet->address = $wallet_address->get_filtered_post_value();

					if ( $this->is_network )
						$wallet->network = $wallet_on_network->is_checked();

					$currency = $this->currencies()->get( $wallet->get_currency_id() );
					$currency->validate_address( $wallet->address );

					$wallet->enabled = $wallet_enabled->is_checked();
					$wallet->confirmations = $confirmations->get_filtered_post_value();

					$wallets->save();
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
				echo $this->$function( $wallet_id );
				return;
			}
		}

		$r .= $form->open_tag();
		$r .= $form->display_form_table();
		$r .= $form->close_tag();

		echo $r;
	}

	/**
		@brief		Admin the license settings.
		@since		2017-12-09 08:19:11
	**/
	public function admin_license()
	{
		echo 'license status and links to purchase';
	}

	/**
		@brief		Show the settings.
		@since		2017-12-09 07:14:33
	**/
	public function admin_settings()
	{
		$form = $this->form();
		$form->css_class( 'plainview_form_auto_tabs' );
		$r = '';

		$fs = $form->fieldset( 'gateway_fees' );
		// Label for fieldset
		$fs->legend->label( __( 'Gateway fees', 'mycryptocheckout' ) );

		$fs->markup( 'm_gateway_fees' )
			->p( __( 'If you wish to charge (or discount) visitors for using MyCryptoCheckout as the payment gateway, you can enter the fixed or percentage amounts in the boxes below. The cryptocurrency checkout price will be modified in accordance with the combined values below.', 'mycryptocheckout' ) );

		$markup_amount = $fs->number( 'markup_amount' )
			->description( __( 'If you wish to mark your prices up (or down) when using cryptocurrency, enter the fixed amount in this box.', 'mycryptocheckout' ) )
			// Input label.
			->label( __( 'Markup amount', 'mycryptocheckout' ) )
			->max( 100 )
			->min( -100 )
			->step( 0.01 )
			->size( 6, 6 )
			->value( $this->get_site_option( 'markup_amount' ) );

		$markup_percent = $fs->number( 'markup_percent' )
			->description( __( 'If you wish to mark your prices up (or down) when using cryptocurrency, enter the percentage in this box.', 'mycryptocheckout' ) )
			// Input label.
			->label( __( 'Markup %', 'mycryptocheckout' ) )
			->max( 100 )
			->min( -100 )
			->step( 0.01 )
			->size( 6, 6 )
			->value( $this->get_site_option( 'markup_percent' ) );


		$this->add_debug_settings_to_form( $form );

		$save = $form->primary_button( 'save' )
			->value( __( 'Save settings', 'mycryptocheckout' ) );

		if ( $form->is_posting() )
		{
			$form->post();
			$form->use_post_values();

			$this->update_site_option( 'markup_amount', $markup_amount->get_filtered_post_value() );
			$this->update_site_option( 'markup_percent', $markup_percent->get_filtered_post_value() );

			$this->save_debug_settings_from_form( $form );
			$r .= $this->info_message_box()->_( __( 'Settings saved!', 'mycryptocheckout' ) );

			echo $r;
			$_POST = [];
			$function = __FUNCTION__;
			echo $this->$function();
			return;
		}

		$r .= $form->open_tag();
		$r .= $form->display_form_table();
		$r .= $form->close_tag();

		echo $r;
	}

	/**
		@brief		Deactivation.
		@since		2017-12-14 08:36:14
	**/
	public function deactivate()
	{
		wp_clear_scheduled_hook( 'mycryptocheckout_hourly' );
	}

	/**
		@brief		init_admin_trait
		@since		2017-12-25 18:25:53
	**/
	public function init_admin_trait()
	{
		$this->add_action( 'mycryptocheckout_hourly' );
	}

	/**
		@brief		Our hourly cron.
		@since		2017-12-22 07:49:38
	**/
	public function mycryptocheckout_hourly()
	{
		$this->api()->account()->retrieve();
	}
}
