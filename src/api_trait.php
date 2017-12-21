<?php

namespace mycryptocheckout;

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
		$this->add_action( 'template_redirect', 'api_template_redirect' );
	}

	/**
		@brief		Get the API controller.
		@since		2017-12-11 14:00:28
	**/
	public function api()
	{
		if ( isset( $this->__api ) )
			return $this->__api;

		$this->__api = new api\API();
		return $this->__api;
	}

	/**
		@brief		Do we need to handle an API call?
		@since		2017-12-09 07:10:48
	**/
	public function api_template_redirect()
	{
		if ( $_SERVER[ 'CONTENT_TYPE' ] != 'application/json' )
			return;
		// Retrieve the body of the request.
		$json = file_get_contents('php://input');
		$json = json_decode( $json );
		if ( ! $json )
			return;

		if ( ! isset( $json->mycryptocheckout ) )
			return;

		try
		{
			$this->api()->process_messages( $json );
			wp_send_json( [ 'result' => 'ok' ] );
		}
		catch ( api\Exception $e )
		{
			wp_send_json( [ 'result' => 'fail', 'message' => $e->get_message() ] );
		}
		exit;
	}

	/**
		@brief		Update the account information hourly.
		@details	Used mostly to update the currency exchange info.
		@since		2017-12-14 08:08:49
	**/
	public function mycryptocheckout_retrieve_account()
	{
		$url = $this->get_server_name();
		return $this->api()->account()->retrieve();
	}
}
