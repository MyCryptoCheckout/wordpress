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
		$next = wp_next_scheduled( 'mycryptocheckout_retrieve_account' );
		wp_unschedule_event( $next, 'mycryptocheckout_retrieve_account' );
		wp_schedule_single_event( time(), 'mycryptocheckout_retrieve_account' );
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
		$table->caption()->text( __( 'Your MyCryptoCheckout account details', 'mycryptocheckout' ) );

		$row = $table->head()->row()->hidden();
		// Table column name
		$row->th( 'key' )->text( __( 'Key', 'mycryptocheckout' ) );
		// Table column name
		$row->td( 'details' )->text( __( 'Details', 'mycryptocheckout' ) );

		if ( $this->debugging() )
		{
			$row = $table->head()->row();
			$row->th( 'key' )->text( __( 'API key', 'mycryptocheckout' ) );
			$row->td( 'details' )->text( $account->get_domain_key() );

			$row = $table->head()->row();
			$row->th( 'key' )->text( __( 'Server name', 'mycryptocheckout' ) );
			$row->td( 'details' )->text( $this->get_server_name() );
		}

		$row = $table->head()->row();
		$row->th( 'key' )->text( __( 'Account data refreshed', 'mycryptocheckout' ) );
		$row->td( 'details' )->text( static::wordpress_ago( $account->data->updated ) );

		if ( $account->has_license() )
		{
			$row = $table->head()->row();
			$text =  __( 'Your license expires', 'mycryptocheckout' );
			$row->th( 'key' )->text( $text );
			$time = $account->get_license_valid_until();
			$text = sprintf( '%s (%s)',
				$this->local_date( $time ),
				human_time_diff( $time )
			);
			$row->td( 'details' )->text( $text );
		}

		$row = $table->head()->row();
		if ( $account->has_license() )
			$text =  __( 'Extend your license', 'mycryptocheckout' );
		else
			$text =  __( 'Purchase a license for unlimited payments', 'mycryptocheckout' );
		$row->th( 'key' )->text( $text );
		$url = $this->api()->get_purchase_url();
		$url = sprintf( '<a href="%s">%s</a> &rArr;',
			$url,
			__( 'MyCryptoCheckout.com pricing page', 'mycryptocheckout' )
		);
		$row->td( 'details' )->text( $url );

		$row = $table->head()->row();
		$row->th( 'key' )->text( __( 'Payments remaining this month', 'mycryptocheckout' ) );
		$row->td( 'details' )->text( $account->get_payments_left_text() );

		$row = $table->head()->row();
		$row->th( 'key' )->text( __( 'Payments processed', 'mycryptocheckout' ) );
		$row->td( 'details' )->text( $account->get_payments_used() );

		$row = $table->head()->row();
		$row->th( 'key' )->text( __( 'Physical currency exchange rates updated', 'mycryptocheckout' ) );
		$row->td( 'details' )->text( static::wordpress_ago( $account->data->physical_exchange_rates->timestamp ) );

		$row = $table->head()->row();
		$row->th( 'key' )->text( __( 'Cryptocurrency exchange rates updated', 'mycryptocheckout' ) );
		$row->td( 'details' )->text( static::wordpress_ago( $account->data->virtual_exchange_rates->timestamp ) );

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

			if ( ! $currency )
			{
				$wallets->forget( $index );
				$wallets->save();
				continue;
			}

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
			->label( __( 'Currency', 'mycryptocheckout' ) );
		$this->currencies()->add_to_select_options( $wallet_currency );

		$wallet_address = $fs->text( 'wallet_address' )
			->description( __( 'The address of your wallet to which you want to receive funds.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Address', 'mycryptocheckout' ) )
			->required()
			->size( 64, 128 )
			->trim();

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

		$r .= wpautop( __( 'You can have several wallets in the same currency. The wallets will be used in sequential order.', 'mycryptocheckout' ) );

		$wallets_text = sprintf(
			// perhaps <a>we can ...you</a>
			__( "If you don't have a wallet address to use, perhaps %swe can recommend some wallets for you%s?", 'mycryptocheckout' ),
			'<a href="https://mycryptocheckout.com/doc/recommended-wallets-exchanges/" target="_blank">',
			'</a>'
		);

		if ( count( $wallets ) < 1 )
			$wallets_text = '<strong>' . $wallets_text . '</strong>';
		$r .= wpautop( $wallets_text );

		$r .= $this->h2( __( 'Current currencies / wallets', 'mycryptocheckout' ) );

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

		$length = $currency->get_address_length();
		if ( is_array( $length ) )
		{
			// Figure out the max length.
			$max = 0;
			foreach( $length as $int )
				$max = max( $max, $int );
			$length = $max;
		}

		$wallet_address = $form->text( 'wallet_address' )
			->description( __( 'The address of your wallet to which you want to receive funds.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Address', 'mycryptocheckout' ) )
			->required()
			->size( $length, $length )
			->trim()
			->value( $wallet->get_address() );

		$wallet_enabled = $form->checkbox( 'wallet_enabled' )
			->checked( $wallet->enabled )
			->description( __( 'Is this wallet enabled and ready to receive funds?', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Enabled', 'mycryptocheckout' ) );

		if ( $currency->supports_confirmations() )
			$confirmations = $form->number( 'confirmations' )
				->description( __( 'How many confirmations needed to regard orders as paid. 1 is the default. More confirmations take longer.', 'mycryptocheckout' ) )
				// Input label
				->label( __( 'Confirmations', 'mycryptocheckout' ) )
				->min( 1, 100 )
				->value( $wallet->confirmations );

		if ( $this->is_network && is_super_admin() )
		{
			$wallet_on_network = $form->checkbox( 'wallet_on_network' )
				->checked( $wallet->network )
				->description( __( 'Do you want the wallet to be available on the whole network?', 'mycryptocheckout' ) )
				// Input label
				->label( __( 'Network wallet', 'mycryptocheckout' ) );

			$sites = $form->select( 'site_ids' )
				->description( __( 'If not network enabled, on which sites this wallet should be available.', 'mycryptocheckout' ) )
				// Input label
				->label( __( 'Sites', 'mycryptocheckout' ) )
				->multiple()
				->value( $wallet->sites );

			foreach( $this->get_sorted_sites() as $site_id => $site_name )
				$sites->option( $site_name, $site_id );

			$sites->autosize();
		}

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
					if ( $currency->supports_confirmations() )
						$wallet->confirmations = $confirmations->get_filtered_post_value();

					if ( $this->is_network && is_super_admin() )
					{
						$wallet->network = $wallet_on_network->is_checked();
						$wallet->sites = $sites->get_post_value();
					}

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

		$fs = $form->fieldset( 'fs_gateway_fees' );
		// Label for fieldset
		$fs->legend->label( __( 'Gateway fees', 'mycryptocheckout' ) );

		$fs->markup( 'm_gateway_fees' )
			->p( __( 'If you wish to charge (or discount) visitors for using MyCryptoCheckout as the payment gateway, you can enter the fixed or percentage amounts in the boxes below. The cryptocurrency checkout price will be modified in accordance with the combined values below. These are applied to the original currency.', 'mycryptocheckout' ) );

		$markup_amount = $fs->number( 'markup_amount' )
			->description( __( 'If you wish to mark your prices up (or down) when using cryptocurrency, enter the fixed amount in this box.', 'mycryptocheckout' ) )
			// Input label.
			->label( __( 'Markup amount', 'mycryptocheckout' ) )
			->max( 1000 )
			->min( -1000 )
			->step( 0.01 )
			->size( 6, 6 )
			->value( $this->get_site_option( 'markup_amount' ) );

		$markup_percent = $fs->number( 'markup_percent' )
			->description( __( 'If you wish to mark your prices up (or down) when using cryptocurrency, enter the percentage in this box.', 'mycryptocheckout' ) )
			// Input label.
			->label( __( 'Markup %', 'mycryptocheckout' ) )
			->max( 1000 )
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
		@brief		Tools
		@since		2017-12-30 23:02:12
	**/
	public function admin_tools()
	{
		$form = $this->form();
		$form->css_class( 'plainview_form_auto_tabs' );
		$r = '';

		$form->markup( 'm_hourly_cron' )
			->p(  __( 'The hourly run cron job will do things like update the account information, exchange rates, send unsent data to the API server, etc.', 'mycryptocheckout' ) );

		$hourly_cron = $form->secondary_button( 'hourly_cron' )
			->value( __( 'Run hourly cron job', 'mycryptocheckout' ) );

		if ( $form->is_posting() )
		{
			$form->post();
			$form->use_post_values();

			if ( $hourly_cron->pressed() )
			{
				do_action( 'mycryptocheckout_hourly' );
				$r .= $this->info_message_box()->_( __( 'MyCryptoCheckout hourly cron job run.', 'mycryptocheckout' ) );
			}

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

		// The plugin table.
		$this->add_filter( 'network_admin_plugin_action_links', 'plugin_action_links', 10, 4 );
		$this->add_filter( 'plugin_action_links', 'plugin_action_links', 10, 4 );
	}

	/**
		@brief		Our hourly cron.
		@since		2017-12-22 07:49:38
	**/
	public function mycryptocheckout_hourly()
	{
		// Schedule an account retrieval sometime.
		// The timestamp shoule be anywhere between now and 45 minutes later.
		$timestamp = rand( 0, 45 ) * 60;
		$timestamp = time() + $timestamp;
		$this->debug( 'Scheduled for %s', $this->local_datetime( $timestamp ) );
		wp_schedule_single_event( $timestamp, 'mycryptocheckout_retrieve_account' );
	}

	/**
		@brief		Modify the plugin links in the plugins table.
		@since		2017-12-30 20:49:13
	**/
	public function plugin_action_links( $links, $plugin_name )
	{
		if ( $plugin_name != 'mycryptocheckout/MyCryptoCheckout.php' )
			return $links;
		if ( is_network_admin() )
			$url = network_admin_url( 'settings.php?page=mycryptocheckout' );
		else
			$url = admin_url( 'options-general.php?page=mycryptocheckout' );
		$links []= sprintf( '<a href="%s">%s</a>',
			$url,
			__( 'Settings', 'threewp_broadcast' )
		);
		return $links;
	}
}
