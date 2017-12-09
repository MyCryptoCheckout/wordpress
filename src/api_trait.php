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
		$this->add_action( 'template_redirect', 'api_template_redirect' );
	}

	/**
		@brief		Do we need to handle an API call?
		@since		2017-12-09 07:10:48
	**/
	public function api_template_redirect()
	{
		if ( ! isset( $_POST[ 'mycryptocheckout' ] ) )
			return;
	}
}
