<?php

namespace mycryptocheckout;

/**
	@brief		Handles the setup of menus.
	@since		2017-12-09 07:05:04
**/
trait menu_trait
{
	/**
		@brief		Init!
		@since		2017-12-07 19:34:05
	**/
	public function init_menu_trait()
	{
		$this->add_action( 'admin_menu' );
		$this->add_action( 'network_admin_menu' );
	}

	/**
		@brief		Admin menu callback.
		@since		2017-12-07 19:35:46
	**/
	public function admin_menu()
	{
		$this->enqueue_js();

		// For normal admin.
		add_submenu_page(
			'options-general.php',
			// Page heading
			__( 'MyCryptoCheckout Settings', 'mycryptocheckout' ),
			// Menu item name
			__( 'MyCryptoCheckout', 'mycryptocheckout' ),
			'manage_options',
			'mycryptocheckout',
			[ &$this, 'admin_menu_tabs' ]
		);

	}

	public function admin_menu_tabs()
	{
		$tabs = $this->tabs();

		$tabs->tab( 'currencies' )
			->callback_this( 'admin_currencies' )
			// Tab heading
			->heading( __( 'MyCryptoCheckout Currencies', 'mycryptocheckout' ) )
			// Name of tab
			->name( __( 'Currencies', 'mycryptocheckout' ) );

		$tabs->tab( 'account' )
			->callback_this( 'admin_account' )
			// Tab heading
			->heading( __( 'MyCryptoCheckout Account', 'mycryptocheckout' ) )
			// Name of tab
			->name( __( 'Account', 'mycryptocheckout' ) );

		$tabs->tab( 'donations' )
			->callback_this( 'admin_donations' )
			// Tab heading
			->heading( __( 'MyCryptoCheckout Donations', 'mycryptocheckout' ) )
			// Name of tab
			->name( __( 'Donations', 'mycryptocheckout' ) );

		if ( $this->is_network )
			$tabs->tab( 'local_settings' )
				->callback_this( 'admin_local_settings' )
				// Tab heading
				->heading( __( 'MyCryptoCheckout Local Settings', 'mycryptocheckout' ) )
				// Name of tab
				->name( __( 'Local Settings', 'mycryptocheckout' ) );

		$tabs->tab( 'global_settings' )
			->callback_this( 'admin_global_settings' )
			// Tab heading
			->heading( __( 'MyCryptoCheckout Global Settings', 'mycryptocheckout' ) )
			// Name of tab
			->name( __( 'Global Settings', 'mycryptocheckout' ) );

		$tabs->tab( 'tools' )
			->callback_this( 'admin_tools' )
			// Tab heading
			->heading( __( 'MyCryptoCheckout Tools', 'mycryptocheckout' ) )
			// Name of tab
			->name( __( 'Tools', 'mycryptocheckout' ) );

		$tabs->tab( 'uninstall' )
			->callback_this( 'admin_uninstall' )
			// Tab heading
			->heading( __( 'Uninstall MyCryptoCheckout', 'mycryptocheckout' ) )
			// Name of tab
			->name( __( 'Uninstall', 'mycryptocheckout' ) )
			->sort_order( 90 );		// Always last.

		if ( $tabs->get_is( 'edit_wallet' ) )
		{
			$wallet_id = $_GET[ 'wallet_id' ];
			$wallets = $this->wallets();
			$wallet = $wallets->get( $wallet_id );
			$tabs->tab( 'edit_wallet' )
				->callback_this( 'admin_edit_wallet' )
				// Editing BTC wallet
				->heading( sprintf(  __( 'Editing %s wallet', 'mycryptocheckout' ), $wallet->get_currency_id() ) )
				// Name of tab
				->name( __( 'Edit wallet', 'mycryptocheckout' ) )
				->parameters( $wallet_id );
		}

		echo $tabs->render();
	}

	/**
		@brief		network_admin_menu
		@since		2017-12-30 20:51:49
	**/
	public function network_admin_menu()
	{
		add_submenu_page(
			'settings.php',
			// Page heading
			__( 'MyCryptoCheckout Settings', 'mycryptocheckout' ),
			// Menu item name
			__( 'MyCryptoCheckout', 'mycryptocheckout' ),
			'manage_options',
			'mycryptocheckout',
			[ &$this, 'admin_menu_tabs' ]
		);
	}

}
