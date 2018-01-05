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
		$this->method_title       = $this->get_method_title();
		$this->method_description = $this->get_method_description();
		$this->has_fields         = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'woocommerce_admin_order_data_after_order_details' ] );
		add_action( 'woocommerce_checkout_create_order', [ $this, 'woocommerce_checkout_create_order' ], 10, 2 );
		add_action( 'woocommerce_email_before_order_table', [ $this, 'woocommerce_email_before_order_table' ], 10, 3 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'post_process_admin_options' ] );
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
		@brief		Return the form fields used for the settings.
		@since		2017-12-30 21:14:39
	**/
	public function get_form_fields()
	{
		$strings = MyCryptoCheckout()->gateway_strings();

		return [
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
				'description' => $strings->get( 'payment_instructions_description' ),
				'default' => $strings->get( 'payment_instructions' ),
			),
			'title' => [
				'title' => __( 'Payment type name', 'mycryptocheckout' ),
				'type' => 'text',
				'description' => __( 'This is the name of the payment option the user will see during checkout.', 'mycryptocheckout' ),
				'default' => $strings->get( 'gateway_name' )
			],
			'currency_selection_text' => [
				'title' => __( 'Text for currency selection', 'mycryptocheckout' ),
				'type' => 'text',
				'description' => __( 'This is the text for the currency selection input.', 'mycryptocheckout' ),
				'default' => $strings->get( 'currency_selection_text' ),
			],
			'your_wallet_address_title_text' => [
				'title' => __( 'Text for user wallet input', 'mycryptocheckout' ),
				'type' => 'text',
				'description' => __( "This is the text for the the input asking for the user's wallet address.", 'mycryptocheckout' ),
				'default' => $strings->get( 'your_wallet_address_title_text' ),
			],
			'your_wallet_address_description_text' => [
				'title' => __( 'Description for user wallet input', 'mycryptocheckout' ),
				'type' => 'text',
				'description' => __( "This is the description for the the input asking for the user's wallet address.", 'mycryptocheckout' ),
				'default' => $strings->get( 'your_wallet_address_description_text' ),
			],
			'reset_to_defaults' => [
				'title'			=> __( 'Reset to defaults', 'mycryptocheckout' ),
				'type'			=> 'checkbox',
				'default'     => 'no',
				'description'	=> __( 'If you wish to reset all of these settings to the defaults, check this box and save your changes.', 'mycryptocheckout' ),
			],
    	];
	}

	/**
		@brief		Return the instructions for this order.
		@since		2017-12-14 19:45:28
	**/
	public function get_instructions( $order_id )
	{
		$instructions = $this->get_option( 'instructions' );
		$payment = MyCryptoCheckout()->api()->payments()->generate_payment_from_order( $order_id );
		$instructions = $payment->replace_shortcodes( $instructions );
		return $instructions;
	}

	/**
		@brief		Return the description of this gateway.
		@since		2017-12-30 21:40:51
	**/
	public function get_method_description()
	{
		$r = __( 'Accept cryptocurrency payments directly into your wallet using the MyCryptoCheckout service.', 'mycryptocheckout' );

		try
		{
			MyCryptoCheckout()->woocommerce->is_available_for_payment();
			$r .= MyCryptoCheckout()->wallets()->build_enabled_string();
		}
		catch ( Exception $e )
		{
			$r .= "\n\n<em>" . __( 'You cannot currently accept any payments using this service:', 'mycryptocheckout' ) . '</em> ' . $e->getMessage();
		}

		$r .= "\n" . sprintf( __( '%sConfigure your wallets here.%s', 'mycryptocheckout' ),
			'<a href="options-general.php?page=mycryptocheckout&tab=currencies">',
			'</a>'
		);

		return $r;
	}

	/**
		@brief		Return the title of this gateway.
		@since		2017-12-30 21:43:30
	**/
	public function get_method_title()
	{
		return 'MyCryptoCheckout';
	}

	/**
		@brief		Init the form fields.
		@since		2017-12-09 22:05:11
	**/
	public function init_form_fields()
	{
		$this->form_fields = $this->get_form_fields();
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
			$this_total = $mcc->markup_amount( $cart_total );
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
		return [
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		];
	}

	/**
		@brief		Handle the resetting of the settings.
		@since		2017-12-30 21:24:38
	**/
	public function post_process_admin_options()
	{
		$this->process_admin_options();

		$reset = $this->get_option( 'reset_to_defaults' );
		if ( $reset != 'yes' )
			return;
		// Reset all of the settings!
		$settings = $this->get_form_fields();
		$new_settings = [];
		foreach( $settings as $key => $field )
		{
			if ( ! isset( $field[ 'default' ] ) )
				continue;
			$default = $field[ 'default' ];
		}
		update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $new_settings ) );
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

		$r = '';
		$r .= sprintf( '<h3>%s</h3>',
			__( 'MyCryptoCheckout details', 'woocommerce' )
		);

		if ( $order->is_paid() )
			$r .= sprintf( '<p class="form-field form-field-wide">%s</p>',
				// Received 123 BTC from abcxyz to xyzabc
				sprintf( __( 'Received %s&nbsp;%s<br/>from %s<br/>to %s', 'mycryptocheckout'),
					$amount,
					$order->get_meta( '_mcc_currency_id' ),
					$order->get_meta( '_mcc_from' ),
					$order->get_meta( '_mcc_to' )
				)
			);
		else
		{
			$r .= sprintf( '<p class="form-field form-field-wide">%s</p>',
				// Expecting 123 BTC from abcxyz to xyzabc
				sprintf( __( 'Expecting %s&nbsp;%s<br/>from %s<br/>to %s', 'mycryptocheckout'),
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
		}

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
		$amount = $mcc->markup_amount( $order_total );
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

		// If paid, do not do anything.
		if ( $order->is_paid() )
			return;

		$instructions = $this->get_instructions( $order->get_id() );
		echo wpautop( wptexturize( $instructions ) ) . PHP_EOL;
	}
}
