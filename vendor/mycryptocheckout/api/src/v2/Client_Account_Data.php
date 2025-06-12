<?php

namespace mycryptocheckout\api\v2;

/**
	@brief		This is the object that is sent to the API server during the account_retrieve command.
	@details	Add whatever other properties you want sent to the server, but they're not going to be stored unless they're known.
	@since		2018-10-13 15:37:38
**/
class Client_Account_Data
{
	/**
		@brief		This is the domain / url of the client.
		@since		2018-10-13 15:41:49
	**/
	public $domain;

	/**
		@brief		The version of the client.
		@details	The first client was a Wordpress plugin, therefore the name.
		@since		2018-10-13 15:42:04
	**/
	public $plugin_version = '0.1';

	/**
	 *	@brief	The temporary key used to tell whether the API contacting us is real.
	 *	@since	2025-06-12 19:31:23
	 **/
	public $retrieve_key;
}
