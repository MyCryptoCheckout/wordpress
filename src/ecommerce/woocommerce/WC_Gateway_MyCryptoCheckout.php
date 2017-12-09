<?php

/**
	@brief		The gateway itself.
	@since		2017-12-08 16:36:26
**/
class WC_Gateway_MyCryptoCheckout extends \WC_Payment_Gateway
{
	/**
		@brief
		@since		2017-12-08 16:45:27
	**/
	public static $gateway_id = 'mycryptocheckout';
	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->form_fields = array(
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'woocommerce' ),
				'label'       => __( 'Enable cash on delivery', 'woocommerce' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
	   );

		$this->init_settings();

		$this->id                 = static::$gateway_id;
		$this->icon               = apply_filters( 'woocommerce_cod_icon', '' );
		$this->method_title       = __( 'MyCryptoCheckout', 'mycryptocheckout' );
		$this->method_description = __( 'Accept cryptocurrency payments directly into your wallet using the MyCryptoCheckout service.', 'woocommerce' );
		$this->has_fields         = false;
		$this->title              = 'Cryptocurrency (eth, btc, etc)';
		$this->description        = 'Description here!';
		//$this->instructions       = $this->get_option( 'instructions' );
		//$this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
		//$this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes' ? true : false;

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		//add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );

		// Customer Emails
		// add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Change payment complete order status to completed for MCC orders.
	 *
	 * @since  3.1.0
	 * @param  string $status
	 * @param  int $order_id
	 * @param  WC_Order $order
	 * @return string
	 */
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order )
			if ( static::$gateway_id === $order->get_payment_method() )
				$status = 'completed';
		return $status;
	}

	/**
		@brief		Is this available?
		@since		2017-12-08 17:20:48
	**/
	public function is_available()
	{
		return true;
		return parent::is_available();
	}

	function payment_fields()
	{
		woocommerce_form_field( 'mcc_currency',
		[
			'type' => 'select',
			'class' => [ 'mcc_currency' ],
			'label' => esc_html__('Payment Currency', 'cryptowoo'),
			'required' => true,
			'options' =>
			[
				'btc' => 'BitCoin BTC',
				'eth' => 'Ethereum ETH',
			],
		] );

		woocommerce_form_field('refund_address',
		[
			'type' => 'text',
			'class' => array('refund-address form-row-full'),
			'label' => esc_html__('Refund Address', 'cryptowoo'),
			'required' => true,
			'placeholder' => '',
			'clear' => true,
		] );
	}

	/**
		@brief		process_admin_options
		@since		2017-12-08 18:51:55
	**/
	public function woocommerce_update_options_payment_gateways_()
	{
	}
}
