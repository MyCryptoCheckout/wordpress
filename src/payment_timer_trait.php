<?php

namespace mycryptocheckout;

use \Exception;

/**
	@brief		Handles the payment timer on the order confirmation page.
	@since		2018-05-01 20:42:55
**/
trait payment_timer_trait
{
	/**
		@brief		Add the payment timer inputs to the settings form.
		@details	Set $form->local_settings if local. Else global is assumed.
		@since		2018-04-26 17:24:27
	**/
	public function add_payment_timer_inputs( $form )
	{
		$payment_timer_enabled = $form->select( 'payment_timer_enabled' )
			// Input description.
			->description( __( 'Enable a payment timer on the order confirmation page.', 'mycryptocheckout' ) )
			// Input label.
			->label( __( 'Payment timer status', 'mycryptocheckout' ) );


		$payment_timer_html = $form->textarea( 'payment_timer_html' )
			// Input description.
			->description( __( 'This is the HTML code used to display the payment timer. Leave empty to use the default value.', 'mycryptocheckout' ) )
			// Input label.
			->label( __( 'Payment timer HTML', 'mycryptocheckout' ) )
			->rows( 5, 40 );

		if ( isset( $form->form()->local_settings ) )
		{
			$payment_timer_enabled->opt( 'enabled', __( 'Enabled', 'mycryptocheckout' ) );
			$payment_timer_enabled->opt( 'disabled', __( 'Disabled', 'mycryptocheckout' ) );
			// Local
			$payment_timer_enabled->value( $this->get_local_option( 'payment_timer_enabled' ) );
			$payment_timer_html->value( $this->get_local_global_file_option( 'payment_timer_html' ) );

			if ( $this->is_network )
				$payment_timer_enabled->opt( 'auto', __( 'Use network admin default', 'mycryptocheckout' ) );
		}
		else
		{
			// Global
			$payment_timer_enabled->opt( 'enabled_all', __( 'Enabled on all sites', 'mycryptocheckout' ) );
			$payment_timer_enabled->opt( 'disabled_all', __( 'Disabled on all sites', 'mycryptocheckout' ) );
			$payment_timer_enabled->opt( 'default_enabled', __( 'Default enabled on all sites', 'mycryptocheckout' ) );
			$payment_timer_enabled->opt( 'default_disabled', __( 'Default disabled on all sites', 'mycryptocheckout' ) );

			$payment_timer_enabled->value( $this->get_site_option( 'payment_timer_enabled' ) );
			$payment_timer_html->value( $this->get_global_file_option( 'payment_timer_html' ) );
		}
	}

	/**
		@brief		Add data to the js.
		@since		2018-04-29 19:23:47
	**/
	public function payment_timer_generate_checkout_javascript_data( $action )
	{
		$html = $this->maybe_enable_option_html( 'payment_timer_enabled', 'payment_timer_html' );
		if ( ! $html )
			return;
		$action->data->set( 'payment_timer_html', $html );
	}

	/**
		@brief		Save the input data.
		@since		2018-04-26 17:25:17
	**/
	public function save_payment_timer_inputs( $form )
	{
		if ( isset( $form->form()->local_settings ) )
		{
			// Local
			$this->update_local_global_disk_option( $form, 'payment_timer_html' );
			$this->update_local_option( 'payment_timer_enabled', $form->input( 'payment_timer_enabled' )->get_post_value() );
		}
		else
		{
			// Global
			$this->update_global_disk_option( $form, 'payment_timer_html' );
			$this->update_site_option( 'payment_timer_enabled', $form->input( 'payment_timer_enabled' )->get_post_value() );
		}
	}
}
