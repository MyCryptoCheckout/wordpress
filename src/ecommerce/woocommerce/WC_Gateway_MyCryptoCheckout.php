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
		$this->id					= \mycryptocheckout\ecommerce\woocommerce\WooCommerce::$gateway_id;
		$icon_file					= $this->generate_icon_file();
		$plugin_dir = plugin_dir_url( __FILE__ );
		$icon_file = $plugin_dir . $icon_file;
		$this->icon					= apply_filters( 'woocommerce_gateway_icon', $icon_file );
		$this->method_title			= $this->get_method_title();
		$this->method_description	= $this->get_method_description();
		$this->has_fields			= true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		add_action( 'woocommerce_email_before_order_table', [ $this, 'woocommerce_email_before_order_table' ], 10, 3 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'post_process_admin_options' ] );
		add_action( 'woocommerce_thankyou_mycryptocheckout', [ $this, 'woocommerce_thankyou_mycryptocheckout' ] );
	}

	/**
		@brief		Generate the SVG icon file dynamically.
		@since		2018-03-16 03:29:14
	**/
	public function generate_icon_file()
	{
		$dir = MyCryptoCheckout()->paths( '__FILE__' );
		$dir = dirname( $dir ) . '/src/static/images/';
		$svg_details = [
			'BTC' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
			'BTG' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
			'BCH' => [
				'width' => 160,
				'offset_left' => 2.5,
			],
			'DASH' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
			'DCR' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
			'ETC' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
			'ETH' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
			'LTC' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
			'NEO' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
			'ZEC' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
			'ERC20' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
			'STAKE' => [
				'width' => 100,
				'offset_left' => 2.5,
			],
		];
		$wallet_options = $this->get_wallet_options();
		$output = file_get_contents( $dir . 'icon_base.svg' );
		$mcc_width = 0;
		$mcc_icons = '';
		$output_filename = 'icons';
		$handled_currencies = [];
		$currency_data = MyCryptoCheckout()->api()->account()->get_currency_data();
		// We do svg_details first and then wallet options in order to get the correct icon order. BTC before BCH, for example.
		foreach( $svg_details as $svg_currency_id => $svg_data )
		{
			foreach( $wallet_options as $currency_id => $ignore )
			{
				// If not found in the array, is this an erc20?
				if ( ! isset( $svg_details[ $currency_id ] ) )
					if ( isset( $currency_data->$currency_id ) )
					{
						$data = $currency_data->$currency_id;
						if ( isset( $data->erc20 ) )
							$currency_id = 'ERC20';
					}

				// Looking for the currencies in the correct order.
				if ( $svg_currency_id != $currency_id )
					continue;

				// Have we already handled this currency?
				if ( isset( $handled_currencies[ $currency_id ] ) )
					continue;

				// We must know about this currency.
				if ( ! isset( $svg_details[ $currency_id ] ) )
					continue;

				// Handle this currency!
				$handled_currencies[ $currency_id ] = true;

				$output_filename .= '_' . $currency_id;
				$svg = $svg_details[ $currency_id ];
				$offset = $mcc_width + $svg[ 'offset_left' ];

				// Insert the icon from disk.
				$icon_svg = file_get_contents( $dir . 'icon_' . $currency_id . '.svg' );
				$icon_svg = str_replace( 'MCC_OFFSET', $offset, $icon_svg );

				$mcc_icons .= $icon_svg;
				$mcc_width += $svg[ 'width' ];
			}
		}

		$output = str_replace( 'MCC_WIDTH', $mcc_width, $output );
		$output = str_replace( 'MCC_ICONS', $mcc_icons, $output );

		$output_filename .= '.svg';
		$output_path = __DIR__ . '/' . $output_filename;
		$output_url = $dir . $output_filename;

		if ( ! file_exists( $output_path ) )
			file_put_contents( $output_path, $output );

		return $output_filename;
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
			'test_mode' => [
				'title'       => __( 'Test mode', 'woocommerce' ),
				'label'       => __( 'Allow purchases to be made without sending any payment information to the MyCryptoCheckout API server.', 'mycryptocheckout' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			],
			'email_instructions' => array(
				'title'       => __( 'E-mail Instructions', 'mycryptocheckout' ),
				'type'        => 'textarea',
				'description' => $strings->get( 'email_payment_instructions_description' ),
				'default' => $strings->get( 'email_payment_instructions' ),
			),
			'online_instructions' => array(
				'title'       => __( 'Online instructions', 'mycryptocheckout' ),
				'type'        => 'textarea',
				'description' => $strings->get( 'online_payment_instructions_description' ),
				'default' => $strings->get( 'online_payment_instructions' ),
			),
			'hide_woocommerce_order_overview' => [
				'title'			=> __( 'Hide order overview', 'mycryptocheckout' ),
				'type'			=> 'checkbox',
				'default'     => 'yes',
				'description'	=> __( 'The order overview is usually placed above crypto payment instructions. Use this option to hide the overview and show the payment instructions higher up.', 'mycryptocheckout' ),
			],
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
			'payment_complete_status' => [
				'title' => __( 'Payment complete status', 'mycryptocheckout' ),
				'type' => 'select',
				'options' => [
					// Order status
					'' => __( 'Processing', 'mycryptocheckout' ),
					// Order status
					'wc-completed' => __( 'Completed', 'mycryptocheckout' ),
				],
				'description' => __( 'After payment is complete, change the order to this status.', 'mycryptocheckout' ),
				'default' => '',
			],
			'payment_timeout_hours' => [
				'title' => __( 'Payment timeout', 'mycryptocheckout' ),
				'type' => 'number',
				'description' => __( 'How many hours to wait for the payment to come through before marking the order as abandoned.', 'mycryptocheckout' ),
				'default' => 6,
				'custom_attributes' =>
				[
					'max' => 72,
					'min' => 1,
					'step' => 1,
				],
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
		@brief		Get the possible wallets as an array of select options.
		@since		2018-03-16 03:30:21
	**/
	public function get_wallet_options()
	{
		$cart = WC()->cart;
		if ( $cart )
			$total = $cart->cart_contents_total;
		else
			$total = 0;
		return MyCryptoCheckout()->get_checkout_wallet_options( [
			'amount' => $total,
			'original_currency' => get_woocommerce_currency(),
		] );

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
		@brief		Return our instance.
		@since		2018-02-09 18:42:13
	**/
	public static function instance()
	{
		$gateways = \WC_Payment_Gateways::instance();
		$gateway = $gateways->payment_gateways();
		return $gateway[ 'mycryptocheckout' ];
	}

	/**
		@brief		Is this available?
		@since		2017-12-08 17:20:48
	**/
	public function is_available()
	{
		$mcc = MyCryptoCheckout();
		// This is to keep the account locked, but still enable checkouts, since this is called twice during the checkout process.
		if ( isset( $mcc->woocommerce->__just_used ) )
			return true;

		try
		{
			$mcc->woocommerce->is_available_for_payment();
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
		MyCryptoCheckout()->enqueue_js();
		MyCryptoCheckout()->enqueue_css();

		$preselected_currencies = MyCryptoCheckout()->wallets()->get_preselected_currency_ids();

		woocommerce_form_field( 'mcc_currency_id',
		[
			'type' => 'select',
			'class' => [ 'mcc_currency' ],
			'default' => reset( $preselected_currencies ),
			'label' => esc_html__( $this->get_option( 'currency_selection_text' ) ),
			'options' => $this->get_wallet_options(),
		] );
	}

	/**
		@brief		Internal method.
		@since		2018-01-26 14:00:58
	**/
	function process_payment( $order_id )
	{
		global $woocommerce;
		$order = new WC_Order( $order_id );

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status( 'pending', __( 'Awaiting cryptocurrency payment', 'mycryptocheckout' ) );

		// Reduce stock levels
		wc_reduce_stock_levels( $order_id );

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
		update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, [] ) );
	}

	/**
		@brief		Prevent the online payment instructions from losing its data-HTML.
		@since		2018-03-23 10:26:07
	**/
	public function validate_textarea_field( $key, $value )
	{
		if ( in_array( $key, [ 'online_instructions' ] ) )
			return trim( stripslashes( $value ) );
		return $this->validate_text_field( $key, $value );
	}

	/**
		@brief		woocommerce_email_before_order_table
		@since		2017-12-10 21:53:27
	**/
	public function woocommerce_email_before_order_table( $order, $sent_to_admin, $plain_text = false )
	{
		if ( $sent_to_admin )
			return;

		if ( $order->get_payment_method() != $this->id )
			return;

		// If paid, do not do anything.
		if ( ! $order->needs_payment() )
			return;

		$instructions = $this->get_option( 'email_instructions' );
		$payment = MyCryptoCheckout()->api()->payments()->generate_payment_from_order( $order->get_id() );
		$instructions = $payment->replace_shortcodes( $instructions );
		echo wpautop( wptexturize( $instructions ) ) . PHP_EOL;
	}

	/**
		@brief		woocommerce_thankyou_mycryptocheckout
		@since		2017-12-10 21:44:51
	**/
	public function woocommerce_thankyou_mycryptocheckout( $order_id )
	{
		$order = wc_get_order( $order_id );
		if ( ! $order->needs_payment() )
			return;

		MyCryptoCheckout()->enqueue_js();
		MyCryptoCheckout()->enqueue_css();
		$instructions = $this->get_option( 'online_instructions' );
		$payment = MyCryptoCheckout()->api()->payments()->generate_payment_from_order( $order_id );
		$instructions = $payment->replace_shortcodes( $instructions );
		if ( ! $instructions )
			return;

		if ( $this->get_option( 'hide_woocommerce_order_overview' ) )
			echo '<div class="hide_woocommerce_order_overview"></div>';

		// If there is a QRcode div in the text, include the qrcode js.
		if ( strpos( $instructions, 'mcc_qr_code' ) !== false )
			wp_enqueue_script( 'mcc_qrcode', MyCryptoCheckout()->paths( 'url' ) . '/src/static/js/qrcode.js', [ 'mycryptocheckout' ], MyCryptoCheckout()->plugin_version );

		echo wpautop( wptexturize( $instructions ) );
	}
}
