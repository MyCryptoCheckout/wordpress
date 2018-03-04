<?php

namespace mycryptocheckout\ecommerce;

/**
	@brief		Base ecommerce class.
	@since		2018-01-06 16:14:25
**/
class Ecommerce
	extends \plainview\sdk_mcc\wordpress\base
{
	/**
		@brief		Find all post IDs with this payment ID and apply the action on them.
		@since		2018-01-06 16:03:22
	**/
	public function do_with_payment_action( $action, $function )
	{
		$payment = $action->payment;
		$switched_blog = 0;
		if ( isset( $payment->data ) )
		{
			$data = json_decode( $payment->data );
			if ( $data )
			{
				if ( isset( $data->site_id ) )
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
		foreach( $results as $order_id )
			$function( $action, $order_id );

		if ( $switched_blog > 0 )
			restore_current_blog();
	}
}
