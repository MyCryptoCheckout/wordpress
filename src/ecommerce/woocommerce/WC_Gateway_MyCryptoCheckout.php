<?php

/**
	@brief		The gateway itself.
	@since		2017-12-08 16:36:26
**/
class WC_Gateway_MyCryptoCheckout extends \WC_Payment_Gateway
{
	/**
		@brief		The ID of the gateway.
		@since		2017-12-08 16:45:27
	**/
	public static $gateway_id = 'mycryptocheckout';

	/**
		@brief		Constructor.
		@since		2017-12-15 08:06:14
	**/
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
		add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'woocommerce_admin_order_data_after_order_details' ] );
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
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false )
	{
		if ( $order )
			if ( static::$gateway_id === $order->get_payment_method() )
				$status = 'completed';
		return $status;
	}

	/**
		@brief		Return the instructions for this order.
		@since		2017-12-14 19:45:28
	**/
	public function get_instructions( $order_id )
	{
		$instructions = $this->get_option( 'instructions' );
		$order = wc_get_order( $order_id );
		$instructions = str_replace( '[AMOUNT]', $order->get_meta( '_mcc_amount' ), $instructions );
		$instructions = str_replace( '[CURRENCY]', $order->get_meta( '_mcc_currency_id' ), $instructions );
		$instructions = str_replace( '[SENDER_WALLET]', $order->get_meta( '_mcc_sender_wallet' ), $instructions );
		$instructions = str_replace( '[RECEIVER_WALLET]', $order->get_meta( '_mcc_receiver_wallet' ), $instructions );
		return $instructions;
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
				'default'     => __( 'Please pay for your order by transfering [AMOUNT] [CURRENCY] from your [SENDER_WALLET] wallet to [RECEIVER_WALLET].', 'mycryptocheckout' ),
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
		// We need at least one wallet.
		$wallets = MyCryptoCheckout()->wallets()->enabled_on_this_site();
		if ( count( $wallets ) < 1 )
			return false;

		// And we need to be able to convert this currency.
		$account = MyCryptoCheckout()->api()->account()->get();
		if ( ! $account->get_physical_exchange_rate( get_woocommerce_currency() ) )
			return false;

		return true;
	}

	/**
		@brief		Show the extra MCC payment fields on the checkout form.
		@since		2017-12-14 19:16:46
	**/
	function payment_fields()
	{
		$mcc = MyCryptoCheckout();

		$cart_total = WC()->cart->cart_contents_total;
		$currencies = $mcc->currencies();
		$wallet_options = [];
		$wallets = $mcc->wallets()->enabled_on_this_site();
		$woocommerce_currency = get_woocommerce_currency();

		foreach( $wallets as $wallet )
		{
			$currency_id = $wallet->get_currency_id();
			$currency = $currencies->get( $currency_id );
			$this_total = \mycryptocheckout\ecommerce\woocommerce\WooCommerce::markup_total( $cart_total );
			$wallet_options[ $currency_id ] = sprintf( '%s (%s %s)',
				$currency->get_name(),
				$currency->convert( $woocommerce_currency, $this_total ),
				$currency_id
			);
		}

		woocommerce_form_field( 'mcc_currency_id',
		[
			'type' => 'select',
			'class' => [ 'mcc_currency' ],
			'label' =>esc_html__( $this->get_option( 'currency_selection_text' ) ),
			'options' => $wallet_options,
		] );

		woocommerce_form_field( 'mcc_sender_address',
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

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status('on-hold', __( 'Awaiting cryptocurrency payment', 'mycryptocheckout' ) );

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
		// Validate the address.
		$mcc = MyCryptoCheckout();
		$currencies = $mcc->currencies();
		$currency_id = sanitize_text_field( $_POST[ 'mcc_currency_id' ] );
		$currency = $currencies->get( $currency_id );

		$sender_address = sanitize_text_field( $_POST[ 'mcc_sender_address' ] );
		try
		{
			$currency->validate_address( $sender_address );
		}
		catch( Exception $e )
		{
			$message = sprintf(
				__( 'The address you specified seems invalid. Could you please double check it? %s', 'mycryptocheckout'),
				$e->getMessage()
			);
			wc_add_notice( $message, 'error' );
			return false;
		}
	}

	/**
		@brief		woocommerce_admin_order_data_after_order_details
		@since		2017-12-14 20:35:48
	**/
	public function woocommerce_admin_order_data_after_order_details( $order )
	{
		$amount = $order->get_meta( '_mcc_amount' );
		if ( ! $amount )
			return;

		$r = '<div class="">';

		$r .= sprintf( '<h3>%s</h3>',
			__( 'MyCryptoCheckout details', 'woocommerce' )
		);

		$r .= sprintf( '<p class="form-field form-field-wide">Expecting %s&nbsp;%s from address %s to address %s</p>',
            	$amount,
            	$order->get_meta( '_mcc_currency_id' ),
            	$order->get_meta( '_mcc_sender_wallet' ),
            	$order->get_meta( '_mcc_receiver_wallet' )
		);

		$r .= '</div>';
		echo $r;
	}

	/**
		@brief		Add the meta fields.
		@since		2017-12-10 21:35:29
	**/
	public function woocommerce_checkout_create_order( $order, $data )
	{
		if ( ! isset( $_POST[ 'mcc_currency_id' ] ) )
			return;
		if ( ! isset( $_POST[ 'mcc_sender_address' ] ) )
			return;

		$currency_id = sanitize_text_field( $_POST[ 'mcc_currency_id' ] );

		// All of the below is just to calculate the amount.
		$mcc = MyCryptoCheckout();

		$order_total = $order->get_total();
		$currencies = $mcc->currencies();
		$currency = $currencies->get( $currency_id );
		$wallet = $mcc->wallets()->get_dustiest_wallet( $currency_id );
		$woocommerce_currency = get_woocommerce_currency();
		$amount = \mycryptocheckout\ecommerce\woocommerce\WooCommerce::markup_total( $order_total );
		$amount = $currency->convert( $woocommerce_currency, $amount );

		$sender_address = sanitize_text_field( $_POST[ 'mcc_sender_address' ] );

		$order->update_meta_data( '_mcc_amount', $amount );
		$order->update_meta_data( '_mcc_currency_id', $currency_id );
		$order->update_meta_data( '_mcc_sender_wallet', $sender_address );
		$order->update_meta_data( '_mcc_receiver_wallet', $wallet->get_address() );
	}

	/**
		@brief		woocommerce_thankyou
		@since		2017-12-10 21:44:51
	**/
	public function woocommerce_thankyou( $order_id )
	{
		$instructions = $this->get_instructions( $order_id );
		if ( ! $instructions )
			return;
		echo wpautop( wptexturize( $instructions ) );
	}

	/**
		@brief		woocommerce_email_before_order_table
		@since		2017-12-10 21:53:27
	**/
	public function woocommerce_email_before_order_table( $order, $sent_to_admin, $plain_text = false )
	{
		if ( $sent_to_admin )
			return;
		if ( $this->id != $order->get_payment_method() )
			return;

		$instructions = $this->get_instructions( $order->get_id() );
		echo wpautop( wptexturize( $instructions ) ) . PHP_EOL;
	}
}


/**
		woocommerce_form_field( '_mcc_amount',
		[
			'type' => 'text',
			'class' => [ 'mcc_amount' ],
			'label' =>  __( 'Cryptocurrency amount', 'mycryptocheckout' ),
			'value' => $amount . ' ' . $order->get_meta( '_mcc_currency_id' )
		] );
    <div class="order_data_column">

        <h4>---------php _e( 'MyCryptoCheckout details', 'woocommerce' ); --------------<a href="#" class="edit_address">---------php _e( 'Edit', 'woocommerce' ); --------------</a></h4>
        <div class="address">
        ---------php
            echo '<p><strong>' . __( 'Some field' ) . ':</strong>' . $order->get_meta( '_some_field' ) . '</p>';
            echo '<p><strong>' . __( 'Another field' ) . ':</strong>' . $order->get_meta( '_another_field' ) . '</p>'; --------------
        </div>
        <div class="edit_address">
            ---------php woocommerce_wp_text_input( array( 'id' => '_some_field', 'label' => __( 'Some field' ), 'wrapper_class' => '_billing_company_field' ) ); --------------
            ---------php woocommerce_wp_text_input( array( 'id' => '_another_field', 'label' => __( 'Another field' ), 'wrapper_class' => '_billing_company_field' ) ); --------------
        </div>
    </div>
**/
