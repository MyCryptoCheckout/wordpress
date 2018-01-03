<?php

namespace mycryptocheckout\ecommerce\easy_digital_downloads;

use Exception;

/**
	@brief		Handles downloads in EDD.
	@since		2018-01-02 15:29:30
**/
class Easy_Digital_Downloads
	extends \plainview\sdk_mcc\wordpress\base
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
		$this->add_action( 'edd_add_email_tags' );
		$this->add_action( 'edd_gateway_' . static::$gateway_id );
		$this->add_action( 'edd_mycryptocheckout_cc_form' );
		$this->add_filter( 'edd_payment_gateways' );
		$this->add_filter( 'edd_settings_gateways' );
		$this->add_filter( 'edd_settings_sections_gateways' );
		$this->add_action( 'edd_view_order_details_billing_after' );
		$this->add_action( 'mycryptocheckout_hourly' );
		$this->add_action( 'mycryptocheckout_payment_complete' );
		$this->add_filter( 'do_shortcode_tag', 10, 4 );
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
		edd_add_email_tag( 'mcc_instructions', $this->get_option_or_default( 'payment_instructions_description' ), function( $payment_id )
		{
			$instructions = $this->get_option_or_default( 'payment_instructions' );
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

		// Check address.
		$from = $purchase_data[ 'post_data' ][ 'mcc_from' ];
		try
		{
			$currency->validate_address( $from );
		}
		catch( Exception $e )
		{
			$message = sprintf(
				__( 'The address you specified seems invalid. Could you please double check it? %s', 'mycryptocheckout'),
				$e->getMessage()
			);
			edd_set_error( 'mcc_from', $message );
		}

		$errors = edd_get_errors();
		if ( $errors )
		{
			edd_send_back_to_checkout( '?payment-mode=' . static::$gateway_id );
			return;
		}

		$amount = edd_get_cart_total();
		$edd_currency = edd_get_currency();
		$amount = $currency->convert( $edd_currency, $amount );

		// Good to go.

		$wallet->use();
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
		edd_update_payment_meta( $payment_id, '_mcc_from', $from );
		edd_update_payment_meta( $payment_id, '_mcc_payment_id', 0 );
		edd_update_payment_meta( $payment_id, '_mcc_to', $wallet->get_address() );

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
		// Assemble the currency options.
		$cart_total = edd_get_cart_total();
		$mcc = MyCryptoCheckout();
		$currencies = $mcc->currencies();
		$edd_currency = edd_get_currency();
		$wallet_options = [];
		$wallets = $mcc->wallets()->enabled_on_this_site();
		foreach( $wallets as $wallet )
		{
			$currency_id = $wallet->get_currency_id();
			$currency = $currencies->get( $currency_id );
			$this_total = $mcc->markup_total( $cart_total );
			$wallet_options[ $currency_id ] = sprintf( '<option value="%s">%s (%s %s)</option>',
				$currency_id,
				$currency->get_name(),
				$currency->convert( $edd_currency, $this_total ),
				$currency_id
			);
		}
		$wallet_options = implode( "\n", $wallet_options );

		ob_start(); ?>
		<fieldset>
			<legend><?php $this->echo_option_or_default( 'gateway_name' ); ?></legend>
			<p>
				<label class="edd-label" for="mcc_currency_id"><?php $this->echo_option_or_default( 'currency_selection_text' ); ?></label>
				<select id="mcc_currency_id" name="mcc_currency_id" class="mcc_currency_id edd-input required">
					<?php echo $wallet_options; ?>
				</select>
			</p>
			<p>
				<label class="edd-label" for="mcc_from"><?php $this->echo_option_or_default( 'your_wallet_address_title_text' ); ?><span class="edd-required-indicator">*</span></label>
				<span class="edd-description"><?php $this->echo_option_or_default( 'your_wallet_address_description_text' ); ?></span>
				<input type="text" id="mcc_from" name="mcc_from" class="mcc_from edd-input required" placeholder="1Agzz6ryfjvALJuDL23AVzNCL5iBULNTgU" required="required"/>
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
				'desc' => sprintf(
					__( "%sConfigure your wallets here.%s", 'mycryptocheckout' ),
					'<a href="options-general.php?page=mycryptocheckout&tab=currencies">',
					'</a>'
				),
				'type' => 'descriptive_text',
			),
			'mcc_info_defaults' => array(
				'id'   => 'mcc_info_defaults',
				'desc' => __( "If left empty, the texts below will use the MyCryptoCheckout defaults.", 'mycryptocheckout' ),
				'type' => 'descriptive_text',
			),
			'mcc_gateway_name' =>
			[
				'id'   => 'mcc_gateway_name',
				'desc' => __( 'This is the name of the payment gateway as visible to the visitor.', 'mycryptocheckout' ),
				'name' => __( 'Gateway name', 'mycryptocheckout' ),
				'size' => 'regular',
				'type' => 'text',
			],
			'mcc_payment_instructions' => array(
				'id' 	=> 'mcc_payment_instructions',
				'name'       => __( 'Instructions', 'mycryptocheckout' ),
				'type'        => 'textarea',
				'desc' => $this->get_option_or_default( 'payment_instructions_description' ),
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
			'mcc_your_wallet_address_title_text' =>
			[
				'id'   => 'mcc_your_wallet_address_title_text',
				'desc' => __( "This is the text for the the input asking for the user's wallet address.", 'mycryptocheckout' ),
				'name' => __( 'Text for user wallet input', 'mycryptocheckout' ),
				'size' => 'regular',
				'type' => 'text',
			],
			'mcc_your_wallet_address_description_text' =>
			[
				'id'   => 'mcc_your_wallet_address_description_text',
				'desc' => __( "This is the description for the the input asking for the user's wallet address.", 'mycryptocheckout' ),
				'name' => __( 'Description for user wallet input', 'mycryptocheckout' ),
				'size' => 'regular',
				'type' => 'text',
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
				$status = __( 'Payment complete', 'mycryptocheckout' );
			else
			{
				$status = __( 'Awaiting blockchain transaction', 'mycryptocheckout' );
				$transaction_id = __( 'Pending', 'mycryptocheckout' );
			}
		}
		else
			$status = __( 'Attempting to contact API server', 'mycryptocheckout' );

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
								<strong class="mcc_from"><?php _e( 'From', 'mycryptocheckout' ); ?></strong><br/>
								<span><?php _e( get_post_meta( $post_id, '_mcc_from', true ) ); ?></span>
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
								<strong class="mcc_payment_id"><?php _e( 'Payment ID', 'mycryptocheckout' ); ?></strong><br/>
								<span><?php _e( get_post_meta( $post_id, '_mcc_payment_id', true ) ); ?></span>
							</p>
						</div>
						<div class="column">
							<p>
								<strong class="mcc_transaction_id"><?php _e( 'Transaction ID', 'mycryptocheckout' ); ?></strong><br/>
								<span><?php _e( $transaction_id ); ?></span>
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
		@brief		mycryptocheckout_payment_complete
		@since		2018-01-02 21:54:53
	**/
	public function mycryptocheckout_payment_complete( $payment )
	{
		if ( ! function_exists( 'EDD' ) )
			return;

		$switched_blog = 0;
		if ( isset( $payment->data ) )
		{
			$data = json_decode( $payment->data );
			if ( $data )
			{
				if ( isset( $data->site_id ) )
					if ( $data->site_id != get_current_blog_id() )
					{
						$switched_blog = $data->site_id;
						switch_to_blog( $switched_blog );
					}
			}
		}

		// Find the payment with this ID.
		global $wpdb;
		$query = sprintf( "SELECT `post_id` FROM `%s` WHERE `meta_key` = '_mcc_payment_id' AND `meta_value` = '%d'",
			$wpdb->postmeta,
			$payment->payment_id
		);
		$results = $wpdb->get_col( $query );
		foreach( $results as $payment_id )
		{
			MyCryptoCheckout()->debug( 'Marking EDD payment %s as complete.', $payment_id );
			update_post_meta( $payment_id, '_mcc_transaction_id', $payment->transaction_id );
			edd_update_payment_status( $payment_id, 'publish' );
		}

		if ( $switched_blog > 0 )
			restore_current_blog();
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

		$payment_id    = edd_get_purchase_id_by_key( $payment_key );

		$instructions = $this->get_option_or_default( 'payment_instructions' );
		$payment = MyCryptoCheckout()->api()->payments()->generate_payment_from_order( $payment_id );
		$instructions = $payment->replace_shortcodes( $instructions );

		$output = wpautop( $instructions ) . $output;
		return $output;
	}
}
