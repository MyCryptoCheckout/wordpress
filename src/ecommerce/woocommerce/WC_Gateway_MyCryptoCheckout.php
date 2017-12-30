<?php

/**
	@brief		The gateway itself.
	@since		2017-12-08 16:36:26
**/
class WC_Gateway_MyCryptoCheckout extends \WC_Payment_Gateway
{
	/**
		@brief		Constructor.
		@since		2017-12-15 08:06:14
	**/
	public function __construct()
	{
		$this->id                 = \mycryptocheckout\ecommerce\woocommerce\WooCommerce::$gateway_id;
		$this->method_title       = 'MyCryptoCheckout';
		$this->method_description = sprintf(
			__( 'Accept cryptocurrency payments directly into your wallet using the MyCryptoCheckout service. %sConfigure your wallets here.%s', 'mycryptocheckout' ),
			'<a href="options-general.php?page=mycryptocheckout">',
			'</a>'
		);
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
			if ( \mycryptocheckout\ecommerce\woocommerce\WooCommerce::$gateway_id === $order->get_payment_method() )
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
		$instructions = str_replace( '[FROM]', $order->get_meta( '_mcc_from' ), $instructions );
		$instructions = str_replace( '[TO]', $order->get_meta( '_mcc_to' ), $instructions );
		return $instructions;
	}

	/**
		@brief		Init the form fields.
		@since		2017-12-09 22:05:11
	**/
	public function init_form_fields()
	{
		try
		{
			MyCryptoCheckout()->woocommerce->is_available_for_payment();
		}
		catch ( Exception $e )
		{
			$message = sprintf( '%s: %s',
				__( 'Payments using this gateway are currently not available', 'woocommerce' ),
				$e->getMessage()
			);
			echo MyCryptoCheckout()->error_message_box()->_( $message );
		}

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
				'description' => __( 'Instructions for payment that will be added to the thank you page. The following shortcodes are available: [AMOUNT], [CURRENCY], [TO], [FROM]', 'mycryptocheckout' ),
				'default'     => __( 'Please pay for your order by transfering [AMOUNT] [CURRENCY] from your [FROM] wallet to [TO].', 'mycryptocheckout' ),
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
		try
		{
			MyCryptoCheckout()->woocommerce->is_available_for_payment();
			return true;
		}
		catch ( Exception $e )
		{
			return false;
		}
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

		$r .= sprintf( '<p class="form-field form-field-wide">%s</p>',
			// Expecting 123 BTC from abcxyz to xyzabc
			sprintf( __( 'Expecting %s&nbsp;%s from %s to %s', 'mycryptocheckout'),
            	$amount,
            	$order->get_meta( '_mcc_currency_id' ),
            	$order->get_meta( '_mcc_from' ),
            	$order->get_meta( '_mcc_to' )
			)
		);

		$attempts = $order->get_meta( '_mcc_attempts' );
		$payment_id = $order->get_meta( '_mcc_payment_id' );

		if ( $payment_id > 0 )
		{
			$r .= sprintf( '<p class="form-field form-field-wide">%s</p>',
				// Expecting 123 BTC from abcxyz to xyzabc
				sprintf( __( 'MyCryptoCheckout payment ID: %d', 'mycryptocheckout'),
					$payment_id
				)
			);
		}
		else
		{
			if ( $attempts > 0 )
				$r .= sprintf( '<p class="form-field form-field-wide">%s</p>',
					sprintf( __( '%d attempts made to contact the API server.', 'mycryptocheckout'),
						$attempts
					)
				);
		}


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

		$wallet->use();
		$mcc->wallets()->save();

		$woocommerce_currency = get_woocommerce_currency();
		$amount = \mycryptocheckout\ecommerce\woocommerce\WooCommerce::markup_total( $order_total );
		$amount = $currency->convert( $woocommerce_currency, $amount );

		$sender_address = sanitize_text_field( $_POST[ 'mcc_sender_address' ] );
		$order->update_meta_data( '_mcc_amount', $amount );
		$order->update_meta_data( '_mcc_currency_id', $currency_id );
		$order->update_meta_data( '_mcc_confirmations', $wallet->confirmations );
		$order->update_meta_data( '_mcc_created_at', time() );
		$order->update_meta_data( '_mcc_from', $sender_address );
		$order->update_meta_data( '_mcc_payment_id', 0 );		// 0 = not sent.
		$order->update_meta_data( '_mcc_to', $wallet->get_address() );
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
