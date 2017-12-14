<?php

namespace mycryptocheckout;

/**
	@brief		Vraious methods that didn't fit anywhere else.
	@since		2017-12-11 14:22:41
**/
trait misc_methods_trait
{
	/**
		@brief		Return the shortest possible name of this server.
		@since		2017-12-11 14:23:01
	**/
	public function get_server_name()
	{
		if ( ! $this->is_network )
		{
			$server_name = get_bloginfo( 'url' );
		}
		else
		{
			// The server name is the name of the first blog.
			$server_name = get_blog_details( 1, 'url' );
		}

		return $server_name;
	}

	/**
		@brief		Site options.
		@since		2017-12-09 09:18:21
	**/
	public function site_options()
	{
		return array_merge( [
			/**
				@brief		The account data used to communicate with the api.
				@details	Json encoded object. Use with $this->api()->account()->get().
				@since		2017-12-11 19:27:46
			**/
			'account_data' => '',
			/**
				@brief		The Wallets collection in which all wallet info is stored.
				@see		Wallets()
				@since		2017-12-09 09:15:52
			**/
			'wallets' => false,
		], parent::site_options() );
	}
}
