<?php
/*
Author:			edward_plainview
Author Email:	edward@plainviewplugins.com
Author URI:		https://plainviewplugins.com
Description:	Cryptocurrency payment gateway using the MyCryptoCheckout.com service.
Domain Path:	/lang
Plugin Name:	MyCryptoCheckout
Plugin URI:		https://mycryptocheckout.com
Text Domain:	mcc
Version:		1.9
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
			$this->easy_digital_downloads = new ecommerce\easy_digital_downloads\Easy_Digital_Downloads();
			$this->woocommerce = new ecommerce\woocommerce\WooCommerce();
		}
	}
}

namespace
{
	define( 'MYCRYPTOCHECKOUT_PLUGIN_VERSION', 1.9 );
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
