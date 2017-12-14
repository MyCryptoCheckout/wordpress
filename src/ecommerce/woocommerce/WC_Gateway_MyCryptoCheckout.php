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
	public function __construct()
	{
		$this->id                 = static::$gateway_id;
		//$this->icon               = apply_filters( 'woocommerce_cod_icon', '' );
		$this->method_title       = 'MyCryptoCheckout';
		$this->method_description = __( 'Accept cryptocurrency payments directly into your wallet using the MyCryptoCheckout service.', 'mycryptocheckout' );
		$this->has_fields         = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		add_action( 'woocommerce_checkout_create_order', [ $this, 'woocommerce_checkout_create_order' ], 10, 2 );
		add_action( 'woocommerce_email_before_order_table', [ $this, 'woocommerce_email_before_order_table' ], 10, 3 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_thankyou_' . $this->id, [ $this, 'woocommerce_thankyou' ] );
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
		@brief		Init the form fields.
		@since		2017-12-09 22:05:11
	**/
	public function init_form_fields()
	{
		$this->form_fields = [
			'enabled' => [
				'title'       => __( 'Enable/Disable', 'woocommerce' ),
				'label'       => __( 'Enable MyCryptoCheckout', 'mycryptocheckout' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			],
			'instructions' => array(
				'title'       => __( 'Instructions', 'mycryptocheckout' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions for payment that will be added to the thank you page. The following shortcodes are available: [AMOUNT], [CURRENCY], [RECEIVER_WALLET], [SENDER_WALLET]', 'mycryptocheckout' ),
				'default'     => __( 'Please transfer [AMOUNT] [CURRENCY] from your [SENDER_WALLET] wallet to [RECEIVER_WALLET].', 'mycryptocheckout' ),
			),
			'title' => [
				'title' => __( 'Payment type name', 'mycryptocheckout' ),
				'type' => 'text',
				'description' => __( 'This is the name of the payment option the user will see during checkout.', 'mycryptocheckout' ),
				'default' => __( 'Cryptocurrency', 'mycryptocheckout' ),
			],
			'currency_selection_text' => [
				'title' => __( 'Text for currency selection', 'mycryptocheckout' ),
				'type' => 'text',
				'description' => __( 'This is the text for the currency selection input.', 'mycryptocheckout' ),
				'default' => __( 'Please select the currency with which you wish to pay', 'mycryptocheckout' ),
			],
			'your_wallet_address_title_text' => [
				'title' => __( 'Text for user wallet input', 'mycryptocheckout' ),
				'type' => 'text',
				'description' => __( "This is the text for the the input asking for the user's wallet address.", 'mycryptocheckout' ),
				'default' => __( "Your wallet address", 'mycryptocheckout' ),
			],
			'your_wallet_address_description_text' => [
				'title' => __( 'Description for user wallet input', 'mycryptocheckout' ),
				'type' => 'text',
				'description' => __( "This is the description for the the input asking for the user's wallet address.", 'mycryptocheckout' ),
				'default' => __( "Your wallet address is used to track the payment.", 'mycryptocheckout' ),
			],

    	];
	}

	/**
		@brief		Is this available?
		@since		2017-12-08 17:20:48
	**/
	public function is_available()
	{
		$wallets = MyCryptoCheckout()->wallets()->enabled_on_this_site();
		return count( $wallets ) > 0;
	}

	function payment_fields()
	{
		$mcc = MyCryptoCheckout();

		$cart_currency = get_woocommerce_currency();
		$cart_total = WC()->cart->cart_contents_total;
		$currencies = $mcc->currencies();
		$wallets = $mcc->wallets()->enabled_on_this_site();

		$options = [];
		foreach( $wallets as $wallet )
		{
			$currency_id = $wallet->get_currency_id();
			$currency = $currencies->get( $currency_id );
			$options[ $currency_id ] = sprintf( '%s (%s %s)',
				$currency->get_name(),
				$currency->convert( $cart_currency, $cart_total ),
				$currency_id
			);
		}

		woocommerce_form_field( 'mcc_currency',
		[
			'type' => 'select',
			'class' => [ 'mcc_currency' ],
			'label' =>esc_html__( $this->get_option( 'currenct_selection_text' ) ),
			'options' => $options,
		] );

		woocommerce_form_field( 'sender_address',
		[
			'type' => 'text',
			'class' => [ 'sender_address form-row-full' ],
			'description' => esc_html__( $this->get_option( 'your_wallet_address_description_text' ) ),
			'label' => esc_html__( $this->get_option( 'your_wallet_address_title_text' ) ),
			'required' => true,
			'placeholder' => '',
		] );
	}

	function process_payment( $order_id )
	{
		global $woocommerce;
		$order = new WC_Order( $order_id );

		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status('on-hold', __( 'Awaiting cryptocurrency payment', 'mycryptocheckout' ));

		// Reduce stock levels
		$order->reduce_order_stock();

		// Remove cart
		$woocommerce->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}

	function validate_fields()
	{
		ddd( $_POST );
		exit;
		return false;
	}

	/**
		@brief		Add the meta fields.
		@since		2017-12-10 21:35:29
	**/
	public function woocommerce_checkout_create_order( $order, $data )
	{
		// don't forget appropriate sanitization if you are using a different field type
		if( isset( $data['some_field'] ) ) {
			$order->update_meta_data( '_some_field', sanitize_text_field( $data['some_field'] ) );
		}
		if( isset( $data['another_field'] ) && in_array( $data['another_field'], array( 'a', 'b', 'c' ) ) ) {
			$order->update_meta_data( '_another_field', $data['another_field'] );
		}
	}

	/**
		@brief		woocommerce_thankyou
		@since		2017-12-10 21:44:51
	**/
	public function woocommerce_thankyou( $order_id )
	{
		if ( ! $this->instructions )
			return;
		// Get our order meta.
		echo wpautop( wptexturize( $this->instructions ) );
	}

	/**
		@brief		woocommerce_email_before_order_table
		@since		2017-12-10 21:53:27
	**/
	public function woocommerce_email_before_order_table( $order, $sent_to_admin, $plain_text = false )
	{
		if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() )
			echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
	}
}
