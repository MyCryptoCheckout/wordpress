<?php

namespace mycryptocheckout\ecommerce\easy_digital_downloads;

use Exception;

/**
	@brief		Handles downloads in EDD.
	@since		2018-01-02 15:29:30
**/
class Easy_Digital_Downloads
	extends \mycryptocheckout\ecommerce\Ecommerce
{
	/**
		@brief		Gateway ID.
		@since		2018-01-02 15:36:17
	**/
	public static $gateway_id = 'mycryptocheckout';

	/**
		@brief		Init!
		@since		2017-12-07 19:34:05
	**/
	public function _construct()
	{
		$this->add_filter( 'do_shortcode_tag', 10, 4 );
		$this->add_action( 'edd_add_email_tags' );
		$this->add_action( 'edd_gateway_' . static::$gateway_id );
		$this->add_action( 'edd_mycryptocheckout_cc_form' );
		$this->add_filter( 'edd_payment_gateways' );
		$this->add_filter( 'edd_settings_gateways' );
		$this->add_filter( 'edd_settings_sections_gateways' );
		$this->add_action( 'edd_view_order_details_billing_after' );
		$this->add_action( 'mycryptocheckout_cancel_payment' );
		$this->add_action( 'mycryptocheckout_complete_payment' );
		$this->add_action( 'mycryptocheckout_hourly' );
	}

	/**
		@brief		Insert our payment info into the edd_receipt shortcode.
		@since		2018-01-03 11:57:26
	**/
	public function do_shortcode_tag( $output, $tag, $p3, $p4 )
	{
		if ( $tag != 'edd_receipt' )
			return $output;

		$leave = edd_get_option( 'mcc_leave_edd_receipt_shortcode_alone' );
		if ( $leave )
			return $output;

		$session = edd_get_purchase_session();
		if ( isset( $_GET['payment_key'] ) ){
			$payment_key = urldecode( $_GET['payment_key'] );
		} else if ( $session ) {
			$payment_key = $session['purchase_key'];
		} elseif ( $edd_receipt_args['payment_key'] ) {
			$payment_key = $edd_receipt_args['payment_key'];
		}

		// No key found
		if ( ! isset( $payment_key ) )
			return;

		MyCryptoCheckout()->enqueue_js();
		MyCryptoCheckout()->enqueue_css();

		$payment_id    = edd_get_purchase_id_by_key( $payment_key );

		$instructions = $this->get_option_or_default( 'online_payment_instructions' );
		$payment = MyCryptoCheckout()->api()->payments()->generate_payment_from_order( $payment_id );
		$instructions = $payment->replace_shortcodes( $instructions );

		$output = wpautop( $instructions ) . $output;
		return $output;
	}

	/**
		@brief		Echo an option or default text for this key.
		@since		2018-01-02 18:17:40
	**/
	public function echo_option_or_default( $key )
	{
		_e( $this->get_option_or_default( $key ) );
	}

	/**
		@brief		Add the instructions email tag.
		@since		2018-01-03 13:26:03
	**/
	public function edd_add_email_tags()
	{
		// We only want the first sentence of the instructions desc.
		$instruction_text = $this->get_option_or_default( 'email_payment_instructions_description' );
		$instruction_text = preg_replace( '/\..*/', '.', $instruction_text );
		edd_add_email_tag( 'mcc_instructions', $instruction_text, function( $payment_id )
		{
			$payment = new \EDD_Payment( $payment_id );
			// Don't show the payment instructions is the payment is paid.
			if ( $payment->status == 'publish' )
				return;

			$instructions = $this->get_option_or_default( 'email_payment_instructions' );
			$payment = MyCryptoCheckout()->api()->payments()->generate_payment_from_order( $payment_id );
			$instructions = $payment->replace_shortcodes( $instructions );
			return $instructions;
		} );
	}

	/**
		@brief		edd_gateway_mycryptocheckout
		@since		2018-01-02 16:51:54
	**/
	public function edd_gateway_mycryptocheckout( $purchase_data )
	{
		$mcc = MyCryptoCheckout();

		// Handle the currency.
		$currencies = $mcc->currencies();
		$currency_id = $purchase_data[ 'post_data' ][ 'mcc_currency_id' ];
		$currency = $currencies->get( $currency_id );
		$wallet = $mcc->wallets()->get_dustiest_wallet( $currency_id );

		$amount = edd_get_cart_total();
		$edd_currency = edd_get_currency();
		$amount = $currency->convert( $edd_currency, $amount );
		$amount = $currency->find_next_available_amount( $amount );

		// Good to go.

		$wallet->use_it();
		$mcc->wallets()->save();

		$payment_data = array(
			'price'         => $purchase_data['price'],
			'date'          => $purchase_data['date'],
			'user_email'    => $purchase_data['user_email'],
			'purchase_key'  => $purchase_data['purchase_key'],
			'currency'      => $edd_currency,
			'downloads'     => $purchase_data['downloads'],
			'user_info'     => $purchase_data['user_info'],
			'cart_details'  => $purchase_data['cart_details'],
			'gateway'       => static::$gateway_id,
			'status'        => 'pending',
		);

		$payment_id = edd_insert_payment( $payment_data );

		edd_update_payment_meta( $payment_id, '_mcc_amount', $amount );
		edd_update_payment_meta( $payment_id, '_mcc_currency_id', $currency_id  );
		edd_update_payment_meta( $payment_id, '_mcc_confirmations', $wallet->confirmations );
		edd_update_payment_meta( $payment_id, '_mcc_created_at', time() );
		$payment_timeout_hours = edd_get_option( 'mcc_payment_timeout_hours' );
		edd_update_payment_meta( $payment_id, '_mcc_payment_timeout_hours', $payment_timeout_hours );

		$test_mode = edd_get_option( 'mcc_test_mode' );
		if ( $test_mode )
		{
			MyCryptoCheckout()->debug( 'In test mode. Not creating payment for EDD order %s', $payment_id );
			$mcc_payment_id = 1;
		}
		else
			$mcc_payment_id = 0;

		edd_update_payment_meta( $payment_id, '_mcc_payment_id', $mcc_payment_id );
		edd_update_payment_meta( $payment_id, '_mcc_to', $wallet->get_address() );

		// Only send it if we are not in test mode.
		if ( $mcc_payment_id < 1 )
			do_action( 'mycryptocheckout_send_payment', $payment_id );

		edd_empty_cart();
		edd_send_to_success_page();
	}

	/**
		@brief		edd_mycryptocheckout_cc_form
		@since		2018-01-02 15:47:24
	**/
	public function edd_mycryptocheckout_cc_form()
	{
		$wallet_options = MyCryptoCheckout()->get_checkout_wallet_options( [
			'as_html' => true,
			'amount' => edd_get_cart_total(),
			'original_currency' => edd_get_currency(),
		] );

		ob_start(); ?>
		<fieldset>
			<legend><?php $this->echo_option_or_default( 'gateway_name' ); ?></legend>
			<p>
				<label class="edd-label" for="mcc_currency_id"><?php $this->echo_option_or_default( 'currency_selection_text' ); ?></label>
				<select id="mcc_currency_id" name="mcc_currency_id" class="mcc_currency_id edd-input required">
					<?php echo $wallet_options; ?>
				</select>
			</p>
		</fieldset>
		<?php
		echo ob_get_clean();
	}

	/**
		@brief		edd_payment_gateways
		@since		2018-01-02 15:40:55
	**/
	public function edd_payment_gateways( $gateways )
	{
		try
		{
			MyCryptoCheckout()->api()->account()->is_available_for_payment();

			$gateways[ static::$gateway_id ] = [
				'admin_label'    => 'MyCryptoCheckout',
				// Checkout label for EDD gateway.
				'checkout_label' => $this->get_option_or_default( 'gateway_name' ),
			];
		}
		catch ( Exception $e )
		{
		}

		return $gateways;
	}

	/**
		@brief		edd_settings_sections_gateways
		@since		2018-01-02 15:31:31
	**/
	public function edd_settings_sections_gateways( $gateway_sections )
	{
		$gateway_sections[ static::$gateway_id ] = 'MyCryptoCheckout';
		return $gateway_sections;
	}

	/**
		@brief		edd_settings_gateways
		@since		2018-01-02 15:56:37
	**/
	public function edd_settings_gateways( $gateway_settings )
	{
		$wallets_text = MyCryptoCheckout()->wallets()->build_enabled_string();
		$wallets_text .= "<br/>";
		$wallets_text .= sprintf(
			__( "%sConfigure your wallets here.%s", 'mycryptocheckout' ),
			'<a href="options-general.php?page=mycryptocheckout&tab=currencies">',
			'</a>'
		);

		$settings = array (
			'mycryptocheckout_settings' =>
			[
				'id'   => 'mycryptocheckout_settings',
				'name' => '<strong>' . __( 'MyCryptoCheckout settings', 'mycryptocheckout' ) . '</strong>',
				'type' => 'header',
			],
			// Leave this here as a placeholder, since inserting things into arrays at specific places is a pain.
			'mcc_info_no_wallets' => array(
				'id'   => 'mcc_info_no_wallets',
				'desc' => '',
				'type' => 'descriptive_text',
			),
			'mcc_info_configure_wallets' => array(
				'id'   => 'mcc_info_configure_wallets',
				'desc' => $wallets_text,
				'type' => 'descriptive_text',
			),
			'mcc_info_defaults' => array(
				'id'   => 'mcc_info_defaults',
				'desc' => __( "If left empty, the texts below will use the MyCryptoCheckout defaults.", 'mycryptocheckout' ),
				'type' => 'descriptive_text',
			),
			'mcc_test_mode' => [
				'id'	=> 'mcc_test_mode',
				'name'	=> __( 'Test mode', 'woocommerce' ),
				'type'	=> 'checkbox',
				'desc'	=> __( 'Allow purchases to be made without sending any payment information to the MyCryptoCheckout API server.', 'mycryptocheckout' ),
			],
			'mcc_gateway_name' =>
			[
				'id'   => 'mcc_gateway_name',
				'desc' => __( 'This is the name of the payment gateway as visible to the visitor.', 'mycryptocheckout' ),
				'name' => __( 'Gateway name', 'mycryptocheckout' ),
				'size' => 'regular',
				'type' => 'text',
			],
			'mcc_email_payment_instructions' => array(
				'id' 	=> 'mcc_email_payment_instructions',
				'name'       => __( 'E-mail instructions', 'mycryptocheckout' ),
				'type'        => 'textarea',
				'desc' => $this->get_option_or_default( 'email_payment_instructions_description' ),
			),
			'mcc_online_payment_instructions' => array(
				'id' 	=> 'mcc_online_payment_instructions',
				'name'       => __( 'Online instructions', 'mycryptocheckout' ),
				'type'        => 'textarea',
				'desc' => $this->get_option_or_default( 'online_payment_instructions_description' ),
			),
			'mcc_leave_edd_receipt_shortcode_alone' =>
			[
				'id'   => 'mcc_leave_edd_receipt_shortcode_alone',
				'desc' => __( "MyCryptoCheckout normally automatically inserts payment instructions into the [edd_receipt] shortcode that is used on the purchase confirmation page.", 'mycryptocheckout' ),
				'name' => __( 'Do not insert payment instructions into the [edd_receipt] shortcode.', 'mycryptocheckout' ),
				'type' => 'checkbox',
			],
			'mcc_currency_selection_text' =>
			[
				'id'   => 'mcc_currency_selection_text',
				'desc' => __( 'This is the text for the currency selection input.', 'mycryptocheckout' ),
				'name' => __( 'Text for currency selection', 'mycryptocheckout' ),
				'size' => 'regular',
				'type' => 'text',
			],
			'mcc_payment_timeout_hours' =>
			[
				'id'   => 'mcc_payment_timeout_hours',
				'default' => 6,
				'desc' => __( 'How many hours to wait for the payment to come through before marking the order as abandoned.', 'mycryptocheckout' ),
				'name' => __( 'Payment timeout', 'mycryptocheckout' ),
				'size' => 'regular',
				'type' => 'number',
				'max' => 72,
				'min' => 1,
				'step' => 1,
			],
			'mcc_reset_to_defaults' => [
				'id'	=> 'mcc_reset_to_defaults',
				'name'	=> __( 'Reset to defaults', 'mycryptocheckout' ),
				'type'	=> 'checkbox',
				'desc'	=> __( 'If you wish to reset all of these settings to the defaults, check this box and save your changes.', 'mycryptocheckout' ),
			],
		);

		if ( edd_get_option( 'mcc_reset_to_defaults' ) )
		{
			global $edd_options;

			foreach( $settings as $key => $ignore )
			{
				unset( $edd_options[ $key ] );
				edd_delete_option( $key );

				// Yepp. EDD requires a prefix. WC doesn't. Smart WC.
				$small_key = str_replace( 'mcc_', '', $key );
				$default = static::get_gateway_string( $small_key );

				if ( isset( $settings[ $key ][ 'default' ] ) )
					$default = $settings[ $key ][ 'default' ];

				if ( $default == '' )
					continue;

				edd_update_option( $key, $default );
			}
		}

		try
		{
			MyCryptoCheckout()->api()->account()->is_available_for_payment();

			$gateways[ static::$gateway_id ] = [
				'admin_label'    => 'MyCryptoCheckout',
				// Checkout label for EDD gateway.
				'checkout_label' => $this->get_option_or_default( 'gateway_name' ),
			];
			unset( $settings[ 'mcc_info_no_wallets' ] );
		}
		catch ( Exception $e )
		{
			$settings[ 'mcc_info_no_wallets' ][ 'desc' ] = sprintf(
				__( 'Warning! Payments using MyCryptoCheckout are not possible: %s', 'mycryptocheckout' ),
				$e->getMessage()
			);
		}

		$gateway_settings[ static::$gateway_id ] = $settings;
		return $gateway_settings;
	}

	/**
		@brief		edd_view_order_details_billing_after
		@since		2018-01-02 20:31:43
	**/
	public function edd_view_order_details_billing_after( $post_id )
	{
		$payment = new \EDD_Payment( $post_id );
		if ( $payment->gateway != static::$gateway_id )
			return;

		$payment_id = get_post_meta( $post_id, '_mcc_payment_id', true );
		if ( $payment_id )
		{
			$transaction_id = get_post_meta( $post_id, '_mcc_transaction_id', true );
			if ( $transaction_id )
			{
				$status = __( 'Payment complete', 'mycryptocheckout' );
				$transaction_id_span = sprintf( '<span title="%s">%s...</span>',
					$transaction_id,
					substr( $transaction_id, 0, 10 )
				);
			}
			else
			{
				if ( $payment_id == 1 )
				{
					$status = __( 'Test', 'mycryptocheckout' );
					$transaction_id = __( 'Test', 'mycryptocheckout' );
				}
				else
				{
					$status = __( 'Awaiting blockchain transaction', 'mycryptocheckout' );
					$transaction_id = __( 'Pending', 'mycryptocheckout' );
				}
				$transaction_id_span = sprintf( '<span>%s</span>',
					$transaction_id
				);
			}
		}
		else
			$status = __( 'Attempting to contact API server', 'mycryptocheckout' );

		$api_payment_id = get_post_meta( $post_id, '_mcc_payment_id', true );
		switch( $api_payment_id )
		{
			case -1:
				$api_payment_id = __( 'Abandoned', 'mycryptocheckout' );
			break;
			case 0:
				$api_payment_id = __( 'Pending', 'mycryptocheckout' );
			break;
			case 1:
				$api_payment_id = __( 'Test', 'mycryptocheckout' );
			break;
		}

		?>
		<div id="mcc_payment_details" class="postbox">
			<h3 class="hndle"><span><?php _e( 'MyCryptoCheckout details', 'mycryptocheckout' ); ?></span></h3>
			<div class="inside">
				<div id="mcc_payment_details_inner">
					<div class="data column-container">
						<div class="column">
							<p>
								<strong class="mcc_amount"><?php _e( 'Amount', 'mycryptocheckout' ); ?></strong><br/>
								<span><?php _e( get_post_meta( $post_id, '_mcc_amount', true ) ); ?> <?php _e( get_post_meta( $post_id, '_mcc_currency_id', true ) ); ?></span>
							</p>
						</div>
						<div class="column">
							<p>
								<strong class="mcc_to"><?php _e( 'To', 'mycryptocheckout' ); ?></strong><br/>
								<span><?php _e( get_post_meta( $post_id, '_mcc_to', true ) ); ?></span>
							</p>
						</div>
					</div><!-- column-container -->
					<div class="data column-container">
						<div class="column">
							<p>
								<strong class="mcc_status"><?php _e( 'Status', 'mycryptocheckout' ); ?></strong><br/>
								<span><?php _e( $status ); ?></span>
							</p>
						</div>
					<?php
						if ( $payment_id ):
					?>
						<div class="column">
							<p>
								<strong class="mcc_payment_id"><?php _e( 'API payment ID', 'mycryptocheckout' ); ?></strong><br/>
								<span><?php _e( $api_payment_id ); ?></span>
							</p>
						</div>
						<div class="column">
							<p>
								<strong class="mcc_transaction_id"><?php _e( 'Transaction ID', 'mycryptocheckout' ); ?></strong><br/>
								<?php _e( $transaction_id_span ); ?>
							</p>
						</div>
					<?php
						else:
					?>
						<div class="column">
							<p>
								<strong class="mcc_attempts"><?php _e( 'API connection attempts', 'mycryptocheckout' ); ?></strong><br/>
								<span><?php _e( intval( get_post_meta( $post_id, '_mcc_attempts', true ) ) ); ?></span>
							</p>
						</div>
					<?php
						endif;
					?>
					</div><!-- column-container -->
				</div><!-- inner -->
				<div class="clear"></div>
			</div><!-- /.inside -->
		</div><!-- /#edd-payment-notes -->
		<?php
	}

	/**
		@brief		The default texts for common gateway text strings.
		@since		2018-01-02 18:19:02
	**/
	public function get_gateway_string( $key )
	{
		return MyCryptoCheckout()->gateway_strings()->get( $key );
	}

	/**
		@brief		get_option_or_default
		@since		2018-01-02 18:18:15
	**/
	public function get_option_or_default( $key )
	{
		// Prefix it with mcc because EDD puts all of its settings in one place (!).
		$r = edd_get_option( 'mcc_' . $key );
		if ( $r == '' )
			$r = static::get_gateway_string( $key );
		return $r;
	}

	/**
		@brief		mycryptocheckout_hourly
		@since		2018-01-02 19:45:08
	**/
	public function mycryptocheckout_hourly()
	{
		if ( ! function_exists( 'EDD' ) )
			return;
		MyCryptoCheckout()->api()->payments()->send_unsent_payments();
	}

	/**
		@brief		Payment was abanadoned.
		@since		2018-01-06 15:59:11
	**/
	public function mycryptocheckout_cancel_payment( $action )
	{
		$this->do_with_payment_action( $action, function( $action, $order_id )
		{
			if ( ! function_exists( 'EDD' ) )
				return;

			// Consider this action finished as soon as we find the order.
			$action->applied++;

			$post = get_post( $order_id );
			if ( $post->post_status != 'pending' )
				return MyCryptoCheckout()->debug( 'Unable to mark EDD payment %s on blog %d as abandoned.', $order_id, get_current_blog_id() );

			MyCryptoCheckout()->debug( 'Marking EDD payment %s on blog %d as abandoned.', $order_id, get_current_blog_id() );
			update_post_meta( $order_id, '_mcc_payment_id', -1 );
			edd_update_payment_status( $order_id, 'abandoned' );
		} );
	}

	/**
		@brief		mycryptocheckout_complete_payment
		@since		2018-01-02 21:54:53
	**/
	public function mycryptocheckout_complete_payment( $payment )
	{
		$this->do_with_payment_action( $payment, function( $action, $order_id )
		{
			if ( ! function_exists( 'EDD' ) )
				return;

			// Consider this action finished as soon as we find the order.
			$action->applied++;

			MyCryptoCheckout()->debug( 'Marking EDD payment %s on blog %d as complete.', $order_id, get_current_blog_id() );
			update_post_meta( $order_id, '_mcc_transaction_id', $action->payment->transaction_id );
			edd_update_payment_status( $order_id, 'publish' );
		} );
	}
}
