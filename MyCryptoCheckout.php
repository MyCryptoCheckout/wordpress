<?php
/*
Author:			edward_plainview
Author Email:	edward@plainviewplugins.com
Author URI:		https://plainviewplugins.com
Description:	Broadcast / multipost posts, with attachments, custom fields and taxonomies to other blogs in the network.
Domain Path:	/lang
Plugin Name:	MyCryptoCheckout
Plugin URI:		https://mycryptocheckout.com
Text Domain:	mcc
Version:		1
*/

namespace mycryptocheckout
{
	require_once( __DIR__ . '/vendor/autoload.php' );

	class MyCryptoCheckout
		extends \plainview\sdk_mcc\wordpress\base
	{
		use \plainview\sdk_mcc\wordpress\traits\debug;

		use admin_trait;
		use api_trait;
		use currencies_trait;
		use wallets_trait;
		use menu_trait;
		use misc_methods_trait;

		/**
			@brief		Constructor.
			@since		2017-12-07 19:31:43
		**/
		public function _construct()
		{
			$this->init_admin_trait();
			$this->init_api_trait();
			$this->init_menu_trait();
			$this->woocommerce = new ecommerce\woocommerce\WooCommerce();

			$this->add_action( 'admin_head' );
		}

		/**
			@brief		Plugins loaded!
			@since		2017-12-21 23:48:35
		**/
		public function admin_head()
		{
			if ( isset( $_GET[ 'hourly' ] ) )
				do_action( 'mycryptocheckout_hourly' );
			return;
			try
			{
				$this->woocommerce->mycryptocheckout_woocommerce_send_payment( 18 );
			}
			catch ( \Exception $e )
			{
				ddd( 'wee' );
				ddd( $e->getMessage() );
			}
		}
	}
}

namespace
{
	DEFINE( 'MYCRYPTOCHECKOUT_VERSION', 1 );

	/**
		@brief		Return the instance of ThreeWP Broadcast.
		@since		2014-10-18 14:48:37
	**/
	function MyCryptoCheckout()
	{
		return mycryptocheckout\MyCryptoCheckout::instance();
	}

	$mycryptocheckout = new mycryptocheckout\MyCryptoCheckout();
}
