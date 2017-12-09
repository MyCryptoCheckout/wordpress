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
		// Check that there is a private key.
		if ( $this->is_network )
		{
		}
		else
		{
		}
	}

	/**
		@brief		Admin the currencies.
		@since		2017-12-09 07:06:56
	**/
	public function admin_currencies()
	{
		$form = $this->form2();
		$form->id( 'broadcast_settings' );
		$form->css_class( 'plainview_form_auto_tabs' );
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
			$row->td( 'currency' )->text( $wallet->get_currency() );
			$row->td( 'wallet' )->text( $wallet->get_address() );
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
					$chosen_currency = $wallet_currency->get_filtered_post_value();
					$currency = $this->currencies()->get( $chosen_currency );

					$wallet = $wallets->new_wallet();
					$wallet->address = $wallet_address->get_filtered_post_value();
					$wallet->currency = $chosen_currency;
					if ( $this->is_network )
						$wallet->network = $wallet_on_network->is_checked();

					$currency->validate_address( $wallet->address );

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

		$r .= wpautop( __( 'The table below shows the wallets you have set up in the plugin. To edit a wallet, click the address.', 'mycryptocheckout' ) );

		$r .= $form->open_tag();
		$r .= $table;
		$r .= $form->close_tag();
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
		echo 'settings';
	}

	/**
		@brief		Site options.
		@since		2017-12-09 09:18:21
	**/
	public function site_options()
	{
		return array_merge( [
			/**
				@brief		The Wallets collection in which all wallet info is stored.
				@see		Wallets()
				@since		2017-12-09 09:15:52
			**/
			'wallets' => false,
		], parent::site_options() );
	}
}
