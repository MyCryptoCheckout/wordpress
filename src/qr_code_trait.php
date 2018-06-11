<?php

namespace mycryptocheckout;

/**
	@brief		For the handling of QR codes.
	@since		2018-05-01 20:42:45
**/
trait qr_code_trait
{
	/**
		@brief		Add the QR code inputs to the settings form.
		@details	Set $form->local_settings if local. Else global is assumed.
		@since		2018-04-26 17:24:27
	**/
	public function add_qr_code_inputs( $form )
	{
		$qr_code_enabled = $form->select( 'qr_code_enabled' )
			// Input description.
			->description( __( 'Enable a QR code for the wallet address on the order confirmation page.', 'mycryptocheckout' ) )
			// Input label.
			->label( __( 'QR code status', 'mycryptocheckout' ) );


		$qr_code_html = $form->textarea( 'qr_code_html' )
			// Input description.
			->description( __( 'This is the HTML code used to display the QR code. Leave empty to use the default value.', 'mycryptocheckout' ) )
			// Input label.
			->label( __( 'QR code HTML', 'mycryptocheckout' ) )
			->rows( 5, 40 );

		if ( isset( $form->form()->local_settings ) )
		{
			$qr_code_enabled->opt( 'enabled', __( 'Enabled', 'mycryptocheckout' ) );
			$qr_code_enabled->opt( 'disabled', __( 'Disabled', 'mycryptocheckout' ) );
			// Local
			$qr_code_enabled->value( $this->get_local_option( 'qr_code_enabled' ) );
			$qr_code_html->value( $this->get_local_global_file_option( 'qr_code_html' ) );

			if ( $this->is_network )
				$qr_code_enabled->opt( 'auto', __( 'Use network admin default', 'mycryptocheckout' ) );
		}
		else
		{
			// Global
			$qr_code_enabled->opt( 'enabled_all', __( 'Enabled on all sites', 'mycryptocheckout' ) );
			$qr_code_enabled->opt( 'disabled_all', __( 'Disabled on all sites', 'mycryptocheckout' ) );
			$qr_code_enabled->opt( 'default_enabled', __( 'Default enabled on all sites', 'mycryptocheckout' ) );
			$qr_code_enabled->opt( 'default_disabled', __( 'Default disabled on all sites', 'mycryptocheckout' ) );

			$qr_code_enabled->value( $this->get_site_option( 'qr_code_enabled' ) );
			$qr_code_html->value( $this->get_global_file_option( 'qr_code_html' ) );
		}
	}

	/**
		@brief		Enqueue the JS.
		@since		2018-05-12 20:35:23
	**/
	public function qr_code_enqueue_js()
	{
		wp_enqueue_script( 'mcc_qrcode', MyCryptoCheckout()->paths( 'url' ) . '/src/static/js/qrcode.js', [ 'mycryptocheckout' ], MyCryptoCheckout()->plugin_version );
	}

	/**
		@brief		Add QR code data to the js.
		@since		2018-04-29 19:23:47
	**/
	public function qr_code_generate_checkout_javascript_data( $action )
	{
		$html = $this->maybe_enable_option_html( 'qr_code_enabled', 'qr_code_html' );
		if ( ! $html )
			return;
		$action->data->set( 'qr_code_html', $html );

		// Assemble all of the qr codes for the various currencies.
		$account = $this->api()->account();
		$qr_codes = [];
		foreach( $account->get_currency_data() as $currency_id => $currency_data )
			if ( ! isset( $currency_data->qr_code ) )
				continue;
			else
				$qr_codes[ $currency_id ] = $currency_data->qr_code;

		$action->data->set( 'qr_codes', $qr_codes );

		$this->qr_code_enqueue_js();
	}

	/**
		@brief		Save the QR code input data.
		@since		2018-04-26 17:25:17
	**/
	public function save_qr_code_inputs( $form )
	{
		if ( isset( $form->form()->local_settings ) )
		{
			// Local
			$this->update_local_global_disk_option( $form, 'qr_code_html' );
			$this->update_local_option( 'qr_code_enabled', $form->input( 'qr_code_enabled' )->get_post_value() );
		}
		else
		{
			// Global
			$this->update_global_disk_option( $form, 'qr_code_html' );
			$this->update_site_option( 'qr_code_enabled', $form->input( 'qr_code_enabled' )->get_post_value() );
		}
	}
}
