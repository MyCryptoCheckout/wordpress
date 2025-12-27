<?php

namespace mycryptocheckout;

use Exception;

/**
	@brief		Handles all things related to the api.
	@since		2017-12-09 07:05:04
**/
trait api_trait
{
	/**
		@brief		Init the api trait.
		@since		2017-12-09 07:10:33
	**/
	public function init_api_trait()
	{
		$this->add_action( 'mycryptocheckout_retrieve_account' );
		$this->add_action( 'mycryptocheckout_send_payment' );
		$this->add_action( 'template_redirect', 'api_template_redirect', 1 );
	}

	/**
		@brief		Get the API controller.
		@since		2017-12-11 14:00:28
	**/
	public function api()
	{
		if ( isset( $this->__api ) )
			return $this->__api;

		$this->__api = new \mycryptocheckout\api\v2\wordpress\API();
		return $this->__api;
	}

	/**
		@brief		Do we need to handle an API call?
		@since		2017-12-09 07:10:48
	**/
	public function api_template_redirect()
	{
		try
		{
			$this->api()->maybe_process_messages();
		}
		catch( Exception $e )
		{
			$this->debug( 'Exception: %s', $e->getMessage() );
		}
	}

	/**
		@brief		Convenience method to update the account data hourly.
		@details	Used mostly to update the currency exchange info.
		@since		2017-12-14 08:08:49
	**/
	public function mycryptocheckout_retrieve_account()
	{
		$this->debug( 'Action mycryptocheckout_retrieve_account called!' );
		$result = $this->api()->account()->retrieve();
		if ( $result )
		{
			$account = $this->api()->account();
			if ( isset( $account->data->license_expired ) )
				if ( $account->data->license_expired )
					$this->expired_license()->add( $account->data->license_expired );
		}
		return $result;
	}

	/**
		@brief		Send a payment to the API.
		@since		2018-01-02 19:19:21
	**/
	public function mycryptocheckout_send_payment( $post_id )
	{
		$this->api()->payments()->send( $post_id );
	}
}
