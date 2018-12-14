<?php

namespace mycryptocheckout\api\v2\wordpress;

use mycryptocheckout\api\v2\Exception;

/**
	@brief		Payment handling.
	@since		2017-12-21 23:28:34
**/
class Payments
	extends \mycryptocheckout\api\v2\Payments
{
	/**
		@brief		Add the Payment data data to the Javascript checkout data.
		@details	This is a convenience function, shared by the e-commerce integrations, to add payment info into the javascript checkout data object.
		@since		2018-09-05 23:22:47
	**/
	public static function add_to_checkout_javascript_data( $action, \mycryptocheckout\api\v2\Payment $payment )
	{
		$action->data->set( 'amount', $payment->amount );
		$action->data->set( 'created_at', $payment->created_at );
		$action->data->set( 'currency_id', $payment->currency_id );

		$currencies = MyCryptoCheckout()->currencies();
		$currency = $currencies->get( $payment->currency_id );
		$action->data->set( 'currency', $currency );
		$action->data->set( 'supports', $currency->supports );

		if ( isset( $payment->paid ) )
			$action->data->set( 'paid', $payment->paid );

		$action->data->set( 'timeout_hours', $payment->timeout_hours );
		$action->data->set( 'to', $payment->to );

		if ( isset( $currency->waves ) )
		{
			$action->data->set( 'token_id', $currency->token_id );
			$action->data->set( 'waves', true );
		}
	}

	/**
		@brief		Cancel a local payment.
		@since		2018-10-13 11:53:18
	**/
	public function cancel_local( \mycryptocheckout\api\v2\Payment $payment )
	{
		$this->do_local( 'cancel_payment', $payment );
	}

	/**
		@brief		Complete a local payment.
		@since		2018-10-13 11:53:39
	**/
	public function complete_local( \mycryptocheckout\api\v2\Payment $payment )
	{
		$this->do_local( 'complete_payment', $payment );
	}

	/**
		@brief		Convenience method to create a new payment.
		@details	Since we could be on a Wordpress network, store the site ID and URL.
		@since		2018-09-20 20:58:53
	**/
	public static function create_new( $data = null )
	{
		$payment = parent::create_new( $data );

		// If we are on a network, then note down the site data.
		if ( MULTISITE )
		{
			$payment->data()->set( 'site_id', get_current_blog_id() );
			$payment->data()->set( 'site_url', get_option( 'siteurl' ) );
		}

		return $payment;
	}

	/**
		@brief		Do this local payment action.
		@details	This is common to both cancel_local and complete_local.
		@since		2018-10-13 11:57:50
	**/
	public function do_local( $message_type, \mycryptocheckout\api\v2\Payment $payment )
	{
		$action = MyCryptoCheckout()->new_action( $message_type );
		$action->payment = $payment;
		$action->execute();
		if ( $action->applied < 1 )
			throw new Exception( sprintf( 'Unable to apply %s for payment ID %s.', $message_type, json_encode( $payment ) ) );
		$this->api()->debug( '%s action applied %s times.', $message_type, $action->applied );
	}

	/**
		@brief		Generate a Payment object from an order.
		@since		2017-12-21 23:47:17
	**/
	public static function generate_payment_from_order( $post_id )
	{
		$payment = static::create_new();

		$payment->amount = get_post_meta( $post_id,  '_mcc_amount', true );
		$payment->confirmations = get_post_meta( $post_id,  '_mcc_confirmations', true );
		$payment->created_at = get_post_meta( $post_id,  '_mcc_created_at', true );
		$payment->currency_id = get_post_meta( $post_id,  '_mcc_currency_id', true );
		$payment->timeout_hours = get_post_meta( $post_id,  '_mcc_payment_timeout_hours', true );
		$payment->to = get_post_meta( $post_id,  '_mcc_to', true );

		$payment->data = get_post_meta( $post_id,  '_mcc_payment_data', true );

		return $payment;
	}

	/**
		@brief		Replace the shortcodes in this string with payment data.
		@since		2018-10-13 13:04:53
	**/
	public static function replace_shortcodes( $payment, $string )
	{
		$string = str_replace( '[AMOUNT]', $payment->amount, $string );
		$string = str_replace( '[CURRENCY]', $payment->currency_id, $string );
		$string = str_replace( '[TO]', $payment->to, $string );
		return $string;
	}

	/**
		@brief		Save this payment for this post.
		@since		2018-10-13 12:42:48
	**/
	public static function save( $post_id, $payment )
	{
		update_post_meta( $post_id, '_mcc_amount', $payment->amount );
		update_post_meta( $post_id, '_mcc_confirmations', $payment->confirmations );
		update_post_meta( $post_id, '_mcc_created_at', $payment->created_at );
		update_post_meta( $post_id, '_mcc_currency_id', $payment->currency_id );
		update_post_meta( $post_id, '_mcc_payment_timeout_hours', $payment->timeout_hours );
		update_post_meta( $post_id, '_mcc_to', $payment->to );
		update_post_meta( $post_id, '_mcc_payment_data', $payment->data );
	}

	/**
		@brief		Send a payment for a post (order) to the API.
		@details	Keep track of attempts and give up if we try too much.
		@since		2018-01-02 19:16:06
	**/
	public function send( $post_id )
	{
		$attempts = intval( get_post_meta( $post_id, '_mcc_attempts', true ) );

		MyCryptoCheckout()->api()->account()->lock()->save();

		try
		{
			$payment = static::generate_payment_from_order( $post_id );
			$payment_id = $this->add( $payment );
			update_post_meta( $post_id, '_mcc_payment_id', $payment_id );
			$this->api()->debug( 'Payment for order %d has been added as payment #%d.', $post_id, $payment_id );
		}
		catch ( Exception $e )
		{
			$attempts++;
			update_post_meta( $post_id, '_mcc_attempts', $attempts );
			$this->api()->debug( 'Failure #%d trying to send the payment for order %d. %s', $attempts, $post_id, $e->getMessage() );
			if ( $attempts > 24 * 60 )	// 24 hours, 60 times an hour, since this is usually run on the hourly cron.
			{
				// TODO: Give up and inform the admin of the failure.
				$this->api()->debug( 'We have now given up on trying to send the payment for order %d.', $post_id );
				update_post_meta( $post_id,  '_mcc_payment_id', -1 );
			}
			else
			{
				// Try again in 5 minutes.
				wp_schedule_single_event( time() + 60, 'mycryptocheckout_send_payment', [ $post_id, microtime() ] );
				$this->api()->debug( 'Will try to send payment %d again.', $post_id );
			}
		}
	}

	/**
		@brief		Find all orders without MCC payment IDs and try to send them.
		@since		2017-12-24 12:11:03
	**/
	public function send_unsent_payments()
	{
		// Find all posts in the database that do not have a payment ID.
		global $wpdb;
		$query = sprintf( "SELECT `post_id` FROM `%s` WHERE `meta_key` = '_mcc_payment_id' AND `meta_value` = '0'",
			$wpdb->postmeta
		);
		$results = $wpdb->get_col( $query );
		if ( count( $results ) < 1 )
			return;
		$this->api()->debug( 'Unsent payments: %s', implode( ', ', $results ) );
		foreach( $results as $post_id )
			$this->send( $post_id );
	}
}
