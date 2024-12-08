<?php

namespace mycryptocheckout;

use Exception;

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
		global $wpdb;

		// Rename the wallets key.
		if ( $this->is_network )
			$wpdb->update( $wpdb->sitemeta, [ 'meta_key' => 'mycryptocheckout\MyCryptoCheckout_wallets' ], [ 'meta_key' => 'mycryptocheckout\MyCryptoCheckout_' ] );
		else
			$wpdb->update( $wpdb->options, [ 'option_name' => 'MyCryptoCheckout_wallets' ], [ 'option_name' => 'MyCryptoCheckout_' ] );

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
		$form->id( 'account' );
		$r = '';

		if ( ! function_exists('curl_version') )
			$r .= $this->error_message_box()->_( __( 'Your PHP CURL module is missing. MyCryptoCheckout may not work 100%% well.', 'mycryptocheckout' ) );

		$public_listing = $form->checkbox( 'public_listing' )
			->checked( $this->get_site_option( 'public_listing' ) )
			->description( __( 'Check the box and refresh your account if you want your webshop listed in the upcoming store directory on mycryptocheckout.com. Your store name and URL will be listed.', 'mycryptocheckout' ) )
			->label( __( 'Be featured in the MCC store directory?', 'mycryptocheckout' ) );

		$retrieve_account = $form->secondary_button( 'retrieve_account' )
			->value( __( 'Refresh your account data', 'mycryptocheckout' ) );

		if ( $this->debugging() )
			$delete_account = $form->secondary_button( 'delete_account' )
				->value( __( 'Delete account data', 'mycryptocheckout' ) );

		if ( $form->is_posting() )
		{
			$form->post();
			$form->use_post_values();

			if ( $this->debugging() )
				if ( $delete_account->pressed() )
				{
					$this->api()->account()->delete();
				}

			if ( $retrieve_account->pressed() )
			{
				if ( $public_listing->is_checked() )
					MyCryptoCheckout()->update_site_option( 'public_listing', true );
				else
					MyCryptoCheckout()->delete_site_option( 'public_listing' );

				$result = $this->mycryptocheckout_retrieve_account();
				if ( $result )
				{
					$r .= $this->info_message_box()->_( __( 'Account data refreshed!', 'mycryptocheckout' ) );
					// Another safeguard to ensure that unsent payments are sent as soon as possible.
					try
					{
						MyCryptoCheckout()->api()->payments()->send_unsent_payments();
					}
					catch( Exception $e )
					{
						$r .= $this->error_message_box()->_( $e->getMessage() );
					}
				}
				else
					$r .= $this->error_message_box()->_( __( 'Error refreshing your account data. Is your site password protected? Do you have coming soon / maintenance mode enabled? A firewall blocking api.mycryptocheckout.com?', 'mycryptocheckout' ) );
			}
		}

		$account = $this->api()->account();

		if ( ! $account->is_valid() )
			$r .= $this->admin_account_invalid();
		else
			$r .= $this->admin_account_valid( $account );

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
		$r .= wpautop( __( 'It appears as if MyCryptoCheckout was unable to retrieve your account data from the API server.', 'mycryptocheckout' ) );
		$r .= wpautop( __( 'Click the Refresh your account data button below to try and retrieve your account data again.', 'mycryptocheckout' ) );
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
				__( 'Payments using MyCryptoCheckout are currently not available', 'mycryptocheckout' ),
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

		}

		$row = $table->head()->row();
		$row->th( 'key' )->text( __( 'Server name', 'mycryptocheckout' ) );
		$row->td( 'details' )->text( $this->get_client_url() );

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
		else
		{
		}

		$row = $table->head()->row();
		if ( $account->has_license() )
			$text =  __( 'Extend your license', 'mycryptocheckout' );
		else
			$text =  __( 'Purchase a license for unlimited payments', 'mycryptocheckout' );
		$row->th( 'key' )->text( $text );
		$url = $this->api()->get_purchase_url();
		$text = $account->has_license() ? __( 'Extend my license', 'mycryptocheckout' ) : __( 'Add an unlimited license to my cart', 'mycryptocheckout' );
		$url = sprintf( '<a href="%s">%s</a> &rArr;',
			$url,
			$text
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

		$wallets = $this->wallets();
		if ( count( $wallets ) > 0 )
		{
			$currencies = $this->currencies();
			$exchange_rates = [];
			foreach( $wallets as $index => $wallet )
			{
				$id = $wallet->get_currency_id();
				if ( isset( $exchange_rates[ $id ] ) )
					continue;
				$currency = $currencies->get( $id );
				if ( $currency )
					$exchange_rates[ $id ] = sprintf( '1 USD = %s %s', $currency->convert( 'USD', 1 ), $id );
				else
					$exchange_rates[ $id ] = sprintf( 'Currency %s is no longer available!', $id );
			}
			ksort( $exchange_rates );
			$exchange_rates = implode( "\n", $exchange_rates );
			$exchange_rates = wpautop( $exchange_rates );
		}
		else
			$exchange_rates = 'n/a';

		$row = $table->head()->row();
		$row->th( 'key' )->text( __( 'Exchange rates for your currencies', 'mycryptocheckout' ) );
		$row->td( 'details' )->text( $exchange_rates );

		if ( $this->debugging() )
		{
			if ( count( (array)$account->data->payment_amounts ) > 0 )
			{
				$row = $table->head()->row();
				$row->th( 'key' )->text( __( 'Reserved amounts', 'mycryptocheckout' ) );
				$text = '';
				$payment_amounts = (array) $account->data->payment_amounts;
				ksort( $payment_amounts );
				foreach( $payment_amounts as $currency_id => $amounts )
				{
					$amounts = (array)$amounts;
					ksort( $amounts );
					$amounts = implode( ', ', array_keys( $amounts ) );
					$text .= sprintf( '<p>%s: %s</p>', $currency_id, $amounts );
				}
				$row->td( 'details' )->text( $text );

				$row = $table->head()->row();
				$row->th( 'key' )->text( __( 'Next scheduled hourly cron', 'mycryptocheckout' ) );
				$next = wp_next_scheduled( 'mycryptocheckout_hourly' );
				$row->td( 'details' )->text( date( 'Y-m-d H:i:s', $next ) );

				$row = $table->head()->row();
				$row->th( 'key' )->text( __( 'Next scheduled account data update', 'mycryptocheckout' ) );
				$next = wp_next_scheduled( 'mycryptocheckout_retrieve_account' );
				$row->td( 'details' )->text( date( 'Y-m-d H:i:s', $next ) );
			}
		}

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
		$form->id( 'currencies' );
		$form->no_automatic_nonce();
		$r = '';

		wp_enqueue_script( 'jquery-ui-sortable' );

		$account = $this->api()->account();
		if ( ! $account->is_valid() )
		{
			$r .= $this->error_message_box()->_( __( 'You cannot modify your currencies until you have a valid account. Please see the Accounts tab.', 'mycryptocheckout' ) );
			echo $r;
			return;
		}

		$table = $this->table();
		$table->css_class( 'currencies' );

		$table->data( 'nonce', wp_create_nonce( 'mycryptocheckout_sort_wallets' ) );

		$table->bulk_actions()
			->form( $form )
			// Bulk action for wallets
			->add( __( 'Delete', 'mycryptocheckout' ), 'delete' )
			// Bulk action for wallets
			->add( __( 'Disable', 'mycryptocheckout' ), 'disable' )
			// Bulk action for wallets
			->add( __( 'Enable', 'mycryptocheckout' ), 'enable' )
			// Bulk action for wallets
			->add( __( 'Mark as used', 'mycryptocheckout' ), 'mark_as_used' )
			// Bulk action for wallets
			->add( __( 'Reset sorting', 'mycryptocheckout' ), 'reset_sorting' );

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
			$row->data( 'index', $index );
			$table->bulk_actions()->cb( $row, $index );
			$currency = $this->currencies()->get( $wallet->get_currency_id() );

			// If the currency is no longer available, delete the wallet.
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
			$url = esc_url( $url );
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
			->css_class( 'currency_id' )
			->description( __( 'Which currency shall the new wallet belong to?', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Currency', 'mycryptocheckout' ) );
		$this->currencies()->add_to_select_options( $wallet_currency );

		$text = __( 'The address of your wallet to which you want to receive funds.', 'mycryptocheckout' );
		$text .= ' ';
		$text .= __( 'If your currency has HD wallet support, you can add your public key when editing the wallet.', 'mycryptocheckout' );
		$wallet_address = $fs->text( 'wallet_address' )
			->description( $text )
			// Input label
			->label( __( 'Address', 'mycryptocheckout' ) )
			->required()
			->size( 64, 128 )
			->trim();

		// This is an ugly hack for Monero. Ideally it would be hidden away in the wallet settings, but for the user it's much nicer here.
		$wallet_address = $fs->text( 'wallet_address' )
			->description( $text )
			// Input label
			->label( __( 'Address', 'mycryptocheckout' ) )
			->required()
			->size( 64, 128 )
			->trim();

		$monero_private_view_key = $fs->text( 'monero_private_view_key' )
			->css_class( 'only_for_currency XMR' )
			->description( __( 'Your private view key that is used to see the amounts in private transactions to your wallet.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Monero private view key', 'mycryptocheckout' ) )
			->placeholder( '157e74dc4e2961c872f87aaf43461f6d0f596f2f116a51fbace1b693a8e3020a' )
			->size( 64, 64 )
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
						$r .= $this->info_message_box()->_( __( 'The selected wallets have been enabled.', 'mycryptocheckout' ) );
					break;
					case 'mark_as_used':
						$ids = $table->bulk_actions()->get_rows();
						foreach( $ids as $id )
						{
							$wallet = $wallets->get( $id );
							$wallet->use_it();
						}
						$wallets->save();
						$r .= $this->info_message_box()->_( __( 'The selected wallets have been marked as used.', 'mycryptocheckout' ) );
					break;
					case 'reset_sorting':
						$ids = $table->bulk_actions()->get_rows();
						foreach( $ids as $id )
						{
							$wallet = $wallets->get( $id );
							$wallet->set_order();
						}
						$wallets->save();
						$r .= $this->info_message_box()->_( __( 'The selected wallets have had their sort order reset.', 'mycryptocheckout' ) );
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

					if ( $currency->supports( 'monero_private_view_key' ) )
						$wallet->set( 'monero_private_view_key', $form->input( 'monero_private_view_key' )->get_filtered_post_value() );

					$wallet->currency_id = $chosen_currency;

					$index = $wallets->add( $wallet );
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

		$r .= wpautop( __( 'This table shows the currencies you have setup. To edit a currency, click the address. To sort them, drag the currency name up or down.', 'mycryptocheckout' ) );

		$r .= wpautop( __( 'If you have several wallets of the same currency, they will be used in sequential order.', 'mycryptocheckout' ) );

		$wallets_text = sprintf(
			// perhaps <a>we can ...you</a>
			__( "If you don't have a wallet address to use, perhaps %swe can recommend some wallets for you%s?", 'mycryptocheckout' ),
			'<a href="https://mycryptocheckout.com/doc/recommended-wallets-exchanges/" target="_blank">',
			'</a>'
		);

		if ( count( $wallets ) < 1 )
			$wallets_text = '<strong>' . $wallets_text . '</strong>';
		$r .= wpautop( $wallets_text );

		// WooCommerce message
		if ( class_exists( 'woocommerce' ) )
		{
			$home_url = home_url();
			$woo_text = sprintf(
				// perhaps <a>WooCommerce Settings</a>
				__( "After adding currencies, visit the %sWooCommerce Settings%s to enable the gateway and more.", 'mycryptocheckout' ),
				'<a href="' . esc_url( $home_url ) . '/wp-admin/admin.php?page=wc-settings&tab=checkout&section=mycryptocheckout">',
				'</a>'
			);
			$r .= wpautop( $woo_text );
		}

		// EDD message
		if ( class_exists( 'Easy_Digital_Downloads' ) )
		{
			$home_url = home_url();
			$edd_text = sprintf(
				// perhaps <a>Easy Digital Downloads Settings</a>
				__( "After adding currencies, visit the %sEasy Digital Downloads Settings%s to enable the gateway and more.", 'mycryptocheckout' ),
				'<a href="' . esc_url( $home_url ) . '/wp-admin/edit.php?post_type=download&page=edd-settings&tab=gateways">',
				'</a>'
			);
			$r .= wpautop( $edd_text );
		}

		$r .= $this->h2( __( 'Current currencies / wallets', 'mycryptocheckout' ) );

		$r .= $form->open_tag();
		$r .= $table;
		$r .= $form->close_tag();
		$r .= $form->open_tag();
		$r .= $form->display_form_table();
		$r .= $form->close_tag();

		$this->enqueue_css();

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
		$this->enqueue_css();
		$wallet = $wallets->get( $wallet_id );

		$currencies = $this->currencies();
		$currency = $currencies->get( $wallet->get_currency_id() );
		$form = $this->form();
		$form->id( 'edit_wallet' );
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

		$fs = $form->fieldset( 'fs_basic' );
		// Fieldset legend
		$fs->legend->label( __( 'Basic settings', 'mycryptocheckout' ) );

		$wallet_label = $fs->text( 'wallet_label' )
			->description( __( 'Describe the wallet to yourself.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Label', 'mycryptocheckout' ) )
			->size( 32 )
			->stripslashes()
			->trim()
			->value( $wallet->get_label() );

		$wallet_address = $fs->text( 'wallet_address' )
			->description( __( 'The address of your wallet to which you want to receive funds.', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Address', 'mycryptocheckout' ) )
			->required()
			->size( $length, $length )
			->trim()
			->value( $wallet->get_address() );

		$ens_address =( $currency->id == 'ETH' || isset( $currency->erc20 ) );
		if ( $ens_address )
		{
			$ens_address_input = $fs->text( 'ens_address' )
				->description( __( 'The ENS address of your wallet to which you want to receive funds. The resolving address must match your normal address.', 'mycryptocheckout' ) )
				// Input label
				->label( __( 'ENS Address', 'mycryptocheckout' ) )
				->size( 32 )
				->trim()
				->value( $wallet->get( 'ens_address' ) );
		}

		$wallet_enabled = $fs->checkbox( 'wallet_enabled' )
			->checked( $wallet->enabled )
			->description( __( 'Is this wallet enabled and ready to receive funds?', 'mycryptocheckout' ) )
			// Input label
			->label( __( 'Enabled', 'mycryptocheckout' ) );

		if ( $currency->supports( 'confirmations' ) )
			$confirmations = $fs->number( 'confirmations' )
				->description( __( 'How many confirmations needed to regard orders as paid. 1 is the default. Only some blockchains support 0-conf (mempool).', 'mycryptocheckout' ) )
				// Input label
				->label( __( 'Confirmations', 'mycryptocheckout' ) )
				->min( 0, 100 )
				->value( $wallet->confirmations );

		if ( $currency->supports( 'btc_hd_public_key' ) )
		{
			if ( ! function_exists( 'gmp_abs' ) )
				$form->markup( 'm_btc_hd_public_key' )
					->markup( __( 'This wallet supports HD public keys, but your system is missing the required PHP GMP library.', 'mycryptocheckout' ) );
			else
			{
				$fs = $form->fieldset( 'fs_btc_hd_public_key' );
				// Fieldset legend
				$fs->legend->label( __( 'HD wallet settings', 'mycryptocheckout' ) );

				$pubs = 'xpub/ypub/zpub';
				if ( $currency->supports( 'btc_hd_public_key_pubs' ) )
					$pubs = implode( '/', $currency->supports->btc_hd_public_key_pubs );

				$btc_hd_public_key = $fs->text( 'btc_hd_public_key' )
					->description( __( sprintf( 'If you have an HD wallet and want to generate a new address after each purchase, enter your %s public key here.', $pubs ), 'mycryptocheckout' ) )
					// Input label
					->label( __( 'HD public key', 'mycryptocheckout' ) )
					->trim()
					->maxlength( 128 )
					->value( $wallet->get( 'btc_hd_public_key' ) );

				$path = $wallet->get( 'btc_hd_public_key_generate_address_path', 0 );
				$btc_hd_public_key_generate_address_path = $fs->number( 'btc_hd_public_key_generate_address_path' )
					->description( __( "The index of the next public wallet address to use. The default is 0 and gets increased each time the wallet is used. This is related to your wallet's gap length.", 'mycryptocheckout' ) )
					// Input label
					->label( __( 'Wallet index', 'mycryptocheckout' ) )
					->min( 0 )
					->value( $path );

				try
				{
					$new_address = $currency->btc_hd_public_key_generate_address( $wallet );
				}
				catch ( Exception $e )
				{
					$new_address = $e->getMessage();
				}
				$fs->markup( 'm_btc_hd_public_key_generate_address_path' )
					->p( __( 'The address at index %d is %s.', 'mycryptocheckout' ), $path, $new_address );

				$circa_amount = $fs->number( 'circa_amount' )
					->description( __( "When using an HD wallet, you can accept amounts that are lower than requested.", 'mycryptocheckout' ) )
					->label( __( 'Payment tolerance percent', 'mycryptocheckout' ) )
					->min( 0 )
					->max( 100 )
					->value( $wallet->get( 'circa_amount' ) );
			}
		}

		if ( $currency->supports( 'monero_private_view_key' ) )
		{
			$monero_private_view_key = $fs->text( 'monero_private_view_key' )
				->description( __( 'Your private view key that is used to see the amounts in private transactions to your wallet.', 'mycryptocheckout' ) )
				// Input label
				->label( __( 'Monero private view key', 'mycryptocheckout' ) )
				->required()
				->size( 64, 64 )
				->trim()
				->value( $wallet->get( 'monero_private_view_key' ) );
		}

		if ( $this->is_network && is_super_admin() )
			$wallet->add_network_fields( $form );

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

					$currency = $this->currencies()->get( $wallet->get_currency_id() );
					$currency->validate_address( $wallet->address );

					$wallet->enabled = $wallet_enabled->is_checked();
					if ( $currency->supports( 'confirmations' ) )
						$wallet->confirmations = $confirmations->get_filtered_post_value();

					if ( $ens_address )
						$wallet->set( 'ens_address', $ens_address_input->get_filtered_post_value() );

					if ( $currency->supports( 'btc_hd_public_key' ) )
						if ( function_exists( 'gmp_abs' ) )
						{
							$public_key = $btc_hd_public_key->get_filtered_post_value();
							$public_key = trim( $public_key );
							$wallet->set( 'btc_hd_public_key', $public_key );
							if ( $public_key != '' )
							{
								// Check that the currency accepts this pub type.
								if ( $currency->supports( 'btc_hd_public_key_pubs' ) )
								{
									$pubs = implode( '/', $currency->supports->btc_hd_public_key_pubs );
									$pub_type = substr( $public_key, 0, 4 );
									if ( ! in_array( $pub_type, $currency->supports->btc_hd_public_key_pubs ) )
										throw new Exception( sprintf( 'This public key type is not supported. Please use only: %s', implode( ' or ', $currency->supports->btc_hd_public_key_pubs ) ) );
								}
								$wallet->set( 'circa_amount', $circa_amount->get_filtered_post_value() );
								$wallet->set( 'btc_hd_public_key_generate_address_path', $btc_hd_public_key_generate_address_path->get_filtered_post_value() );
							}

						}

					$wallet->maybe_parse_network_form_post( $form );

					$wallet->set_label( $wallet_label->get_filtered_post_value() );

					if ( $currency->supports( 'monero_private_view_key' ) )
					{
						foreach( [ 'monero_private_view_key' ] as $key )
							$wallet->set( $key, $$key->get_filtered_post_value() );
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
		@brief		Local settings.
		@since		2018-04-26 16:14:39
	**/
	public function admin_local_settings()
	{
		$form = $this->form();
		$form->id( 'local_settings' );
		$form->css_class( 'plainview_form_auto_tabs' );
		$form->local_settings = true;
		$r = '';

		$fs = $form->fieldset( 'fs_qr_code' );
		// Label for fieldset
		$fs->legend->label( __( 'QR code', 'mycryptocheckout' ) );

		$this->add_qr_code_inputs( $fs );

		$fs = $form->fieldset( 'fs_payment_timer' );
		// Label for fieldset
		$fs->legend->label( __( 'Payment timer', 'mycryptocheckout' ) );

		$this->add_payment_timer_inputs( $fs );

		$save = $form->primary_button( 'save' )
			->value( __( 'Save settings', 'mycryptocheckout' ) );

		if ( $form->is_posting() )
		{
			$form->post();
			$form->use_post_values();

			$this->save_qr_code_inputs( $form );
			$this->save_payment_timer_inputs( $form );

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
		@brief		Show the settings.
		@since		2017-12-09 07:14:33
	**/
	public function admin_global_settings()
	{
		$form = $this->form();
		$form->id( 'global_settings' );
		$form->css_class( 'plainview_form_auto_tabs' );
		$r = '';

		$fs = $form->fieldset( 'fs_gateway_fees' );
		// Label for fieldset
		$fs->legend->label( __( 'Gateway fees', 'mycryptocheckout' ) );

		$fs->markup( 'm_gateway_fees' )
			->p( __( 'If you wish to charge (or discount) visitors for using MyCryptoCheckout as the payment gateway, you can enter the fixed or percentage amounts in the boxes below. The cryptocurrency checkout price will be modified in accordance with the combined values below. These are applied to the original currency.', 'mycryptocheckout' ) );

		$markup_amount = $fs->number( 'markup_amount' )
			// Input description.
			->description( __( 'If you wish to mark your prices up (or down) when using cryptocurrency, enter the fixed amount in this box.', 'mycryptocheckout' ) )
			// Input label.
			->label( __( 'Markup amount', 'mycryptocheckout' ) )
			->max( 1000 )
			->min( -1000 )
			->step( 0.01 )
			->size( 6, 6 )
			->value( $this->get_site_option( 'markup_amount' ) );

		$markup_percent = $fs->number( 'markup_percent' )
			// Input description.
			->description( __( 'If you wish to mark your prices up (or down) when using cryptocurrency, enter the percentage in this box.', 'mycryptocheckout' ) )
			// Input label.
			->label( __( 'Markup %%', 'mycryptocheckout' ) )
			->max( 1000 )
			->min( -100 )
			->step( 0.01 )
			->size( 6, 6 )
			->value( $this->get_site_option( 'markup_percent' ) );

		$fs = $form->fieldset( 'fs_qr_code' );
		// Label for fieldset
		$fs->legend->label( __( 'QR code', 'mycryptocheckout' ) );

		if ( $this->is_network )
			$form->global_settings = true;
		else
			$form->local_settings = true;

		$this->add_qr_code_inputs( $fs );

		$fs = $form->fieldset( 'fs_payment_timer' );
		// Label for fieldset
		$fs->legend->label( __( 'Payment timer', 'mycryptocheckout' ) );

		if ( $this->is_network )
			$form->global_settings = true;
		else
			$form->local_settings = true;

		$this->add_payment_timer_inputs( $fs );

		$this->add_debug_settings_to_form( $form );

		$save = $form->primary_button( 'save' )
			->value( __( 'Save settings', 'mycryptocheckout' ) );

		if ( $form->is_posting() )
		{
			$form->post();
			$form->use_post_values();

			$this->update_site_option( 'markup_amount', $markup_amount->get_filtered_post_value() );
			$this->update_site_option( 'markup_percent', $markup_percent->get_filtered_post_value() );

			$this->save_payment_timer_inputs( $form );
			$this->save_qr_code_inputs( $form );

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
		$form->id( 'tools' );
		$form->css_class( 'plainview_form_auto_tabs' );
		$r = '';

		$form->markup( 'm_hourly_cron' )
			->p( __( 'The hourly run cron job will do things like update the account information, exchange rates, send unsent data to the API server, etc.', 'mycryptocheckout' ) );

		$hourly_cron = $form->secondary_button( 'hourly_cron' )
			->value( __( 'Run hourly cron job', 'mycryptocheckout' ) );

		$form->markup( 'm_test_communication' )
			->p( __( "Test the communication with the API server. If it doesn't work, then there is a conflict with another plugin or the theme.", 'mycryptocheckout' ) );

		$test_communication = $form->secondary_button( 'test_communication' )
			->value( __( 'Test communication', 'mycryptocheckout' ) );

		$form->markup( 'm_show_expired_license_notifications' )
			->p(  __( 'Make all expired license notifications appear again.', 'mycryptocheckout' ) );

		$show_expired_license_notifications = $form->secondary_button( 'show_expired_license_notifications' )
			->value( __( 'Reset expired license notifications', 'mycryptocheckout' ) );

		if ( $form->is_posting() )
		{
			$form->post();
			$form->use_post_values();

			if ( $hourly_cron->pressed() )
			{
				do_action( 'mycryptocheckout_hourly' );
				$r .= $this->info_message_box()->_( __( 'MyCryptoCheckout hourly cron job run.', 'mycryptocheckout' ) );
			}

			if ( $test_communication->pressed() )
			{
				$result = $this->api()->test_communication();
				if ( $result->result == 'ok' )
					$r .= $this->info_message_box()->_( __( 'Success! %s', 'mycryptocheckout' ), $result->message );
				else
					$r .= $this->error_message_box()->_( __( 'Communications failure: %s', 'mycryptocheckout' ),
						$result->message
					);
			}

			if ( $show_expired_license_notifications->pressed() )
			{
				$this->update_site_option( 'expired_license_nag_dismissals', [] );
				$r .= $this->info_message_box()->_( __( 'Notifications reset! The next time your account is refreshed, you might see an expired license notification. ', 'mycryptocheckout' ) );
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

		// Sort the wallets.
		$this->add_action( 'wp_ajax_mycryptocheckout_sort_wallets' );

		// Display the expired warning?
		$this->expired_license()->show();
	}

	/**
		@brief		Our hourly cron.
		@since		2017-12-22 07:49:38
	**/
	public function mycryptocheckout_hourly()
	{
		// Schedule an account retrieval sometime.
		// The timestamp shoule be anywhere between soon and 50 minutes later.
		$extra = rand( 5, 50 ) * 60;
		$timestamp = time() + $extra;
		$this->debug( 'Hourly running. Scheduled mycryptocheckout_retrieve_account at %s for %s + %s', $this->local_datetime( $timestamp ), time(), $extra );
		$next = wp_next_scheduled( 'mycryptocheckout_retrieve_account' );
		wp_unschedule_event( $next, 'mycryptocheckout_retrieve_account' );
		wp_schedule_single_event( $timestamp, 'mycryptocheckout_retrieve_account' );
		$next = wp_next_scheduled( 'mycryptocheckout_retrieve_account' );
		$this->debug( 'Next schedule: %s', date( 'Y-m-d H:i:s', $next ) );
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
			__( 'Settings', 'mycryptocheckout' )
		);
		return $links;
	}

	/**
		@brief		Allow the user to sort the wallets via ajax.
		@since		2018-10-17 18:54:22
	**/
	public function wp_ajax_mycryptocheckout_sort_wallets()
	{
		if ( ! isset( $_REQUEST[ 'nonce' ] ) )
			wp_die( 'No nonce.' );
		$nonce = $_REQUEST[ 'nonce' ];

		if ( ! wp_verify_nonce( $nonce, 'mycryptocheckout_sort_wallets' ) )
			wp_die( 'Invalid nonce.' );

		// Load the wallets.
		$wallets = $this->wallets();

		foreach( $wallets as $wallet_id => $wallet )
		{
			foreach( $_POST[ 'wallets' ] as $wallet_order => $post_wallet_id )
			{
				if ( $wallet_id != $post_wallet_id )
					continue;
				$wallet->set_order( $wallet_order );
			}
		}

		$wallets->save();
	}
}
