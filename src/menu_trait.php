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
		$this->add_action( 'network_admin_menu', 'admin_menu' );
	}

	/**
		@brief		Admin menu callback.
		@since		2017-12-07 19:35:46
	**/
	public function admin_menu()
	{
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
			// Name of tab
			->name( __( 'Currencies', 'mycryptocheckout' ) );

		$tabs->tab( 'license' )
			->callback_this( 'admin_license' )
			// Name of tab
			->name( __( 'License', 'mycryptocheckout' ) );

		$tabs->tab( 'settings' )
			->callback_this( 'admin_settings' )
			// Name of tab
			->name( __( 'Settings', 'mycryptocheckout' ) );

		$tabs->tab( 'uninstall' )
			->callback_this( 'admin_uninstall' )
			// Name of tab
			->name( __( 'Uninstall', 'mycryptocheckout' ) )
			->sort_order( 90 );		// Always last.

		echo $tabs->render();
	}
}
