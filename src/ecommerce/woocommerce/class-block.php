<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Mycryptocheckout_Gateway_Blocks extends AbstractPaymentMethodType
{
    protected $name = 'mycryptocheckout'; // Payment gateway name.

    public function initialize()
    {
		require_once( __DIR__ . DIRECTORY_SEPARATOR . 'WC_Gateway_MyCryptoCheckout.php' );
        $this->settings = get_option( 'woocommerce_mycryptocheckout_settings', [] );
    }

    public function is_active()
    {
        return $this->gateway()->is_available();
    }

    /**
     * Return the gateway instance.
     *
     * @since		2025-03-11 17:40:49
     **/
    public function gateway()
    {
        return WC_Gateway_MyCryptoCheckout::instance();
    }

    public function get_payment_method_script_handles()
    {
        wp_register_script(
            'mycryptocheckout_gateway_blocks_integration',
            plugin_dir_url(__FILE__) . '/js/index.js',
            [		// dependencies
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            MYCRYPTOCHECKOUT_PLUGIN_VERSION,
            true
        );

        /**
        if( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'mycryptocheckout_gateway_blocks_integration');
        }
        **/

        return [ 'mycryptocheckout_gateway_blocks_integration' ];
    }

    public function get_payment_method_data()
    {
        ob_start();
        echo $this->gateway()->woocommerce_gateway_icon( '', \mycryptocheckout\ecommerce\woocommerce\WooCommerce::$gateway_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is HTML for icons; attributes are escaped internally.
		$this->gateway()->payment_fields();
		$pf = ob_get_clean();

        return [
            'title' => $this->gateway()->title,
            'payment_fields' => $pf,
        ];
    }

}
?>