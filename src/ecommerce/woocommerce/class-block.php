<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Mycryptocheckout_Gateway_Blocks extends AbstractPaymentMethodType
{
    private $gateway;
    protected $name = 'mycryptocheckout';// your payment gateway name

    public function initialize()
    {
    	require_once( __DIR__ . DIRECTORY_SEPARATOR . 'WC_Gateway_MyCryptoCheckout.php' );
        $this->settings = get_option( 'woocommerce_mycryptocheckout_settings', [] );
        $this->gateway = WC_Gateway_MyCryptoCheckout::instance();
    }

    public function is_active()
    {
    	$r =  $this->gateway->is_available();
    	return $r;
    }

    public function get_payment_method_script_handles()
    {
        wp_register_script(
            'mycryptocheckout_gateway_blocks_integration',
            plugin_dir_url(__FILE__) . 'checkout.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );
        if( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'mycryptocheckout_gateway_blocks_integration');

        }
        return [ 'mycryptocheckout_gateway_blocks_integration' ];
    }

    public function get_payment_method_data()
    {
        return [
            'title' => $this->gateway->title,
            //'description' => $this->gateway->description,
        ];
    }

}
?>