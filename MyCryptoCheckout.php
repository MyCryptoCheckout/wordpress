<?php
/*
Author:				edward_plainview
Author Email:		edward@plainviewplugins.com
Author URI:			https://plainviewplugins.com
Description:		Cryptocurrency payment gateway for WooCommerce and Easy Digital Downloads.
Plugin Name:		MyCryptoCheckout
Plugin URI:			https://mycryptocheckout.com
Text Domain:		mycryptocheckout
Version:			2.141
WC tested up to:	9.4.1
*/

namespace mycryptocheckout
{
	require_once( __DIR__ . '/vendor/autoload.php' );

	class MyCryptoCheckout
		extends \plainview\sdk_mcc\wordpress\base
	{
		/**
			@brief		Plugin version.
			@since		2018-03-14 19:04:03
		**/
		public $plugin_version = MYCRYPTOCHECKOUT_PLUGIN_VERSION;

		use \plainview\sdk_mcc\wordpress\traits\debug;

		use admin_trait;
		use api_trait;
		use autosettlement_trait;
		use currencies_trait;
		use donations_trait;
		use wallets_trait;
		use menu_trait;
		use misc_methods_trait;
		use qr_code_trait;
		use payment_timer_trait;

		/**
			@brief		Constructor.
			@since		2017-12-07 19:31:43
		**/
		public function _construct()
		{
			$this->init_admin_trait();
			$this->init_api_trait();
			$this->init_currencies_trait();
			$this->init_donations_trait();
			$this->init_menu_trait();
			$this->init_misc_methods_trait();
			$this->easy_digital_downloads = new ecommerce\easy_digital_downloads\Easy_Digital_Downloads();
			$this->woocommerce = new ecommerce\woocommerce\WooCommerce();

			add_action( 'before_woocommerce_init', function()
			{
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
				}
			} );

			if ( defined( 'WP_CLI' ) && WP_CLI )
			{
				$cli = new cli\MyCryptoCheckout();
				\WP_CLI::add_command( 'mycryptocheckout', $cli );
			}
		}
	}
}

namespace
{
	define( 'MYCRYPTOCHECKOUT_PLUGIN_VERSION', 2.141 );
	/**
		@brief		Return the instance of MCC.
		@since		2014-10-18 14:48:37
	**/
	function MyCryptoCheckout()
	{
		return mycryptocheckout\MyCryptoCheckout::instance();
	}

	$mycryptocheckout = new mycryptocheckout\MyCryptoCheckout( __FILE__ );
}
