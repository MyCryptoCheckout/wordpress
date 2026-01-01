<?php

namespace mycryptocheckout\cli;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Exception;
use WP_CLI;

/**
Generic commands for MCC.

@since		2019-01-09 14:15:09
**/
class MyCryptoCheckout
{
	/**
		@brief		Dump public keys
		@since		2020-04-24 21:49:01
	**/
	public function dump_pub( $args )
	{
		$dump_pub = new Dump_Pub( $this );
		$dump_pub->run( $args );
	}

	/**
		Run internal tests.

		* @since		2019-01-09 14:16:42
	**/
	public function tests()
	{
		$tests = new Tests( $this );
		$tests->run();
	}

	/**
		Request an account update.
		@since		2019-01-09 14:17:48
	**/
	public function update_account()
	{
		$result = MyCryptoCheckout()->mycryptocheckout_retrieve_account();
		if ( $result )
			WP_CLI::line( 'Account info updated. :) ' );
		else
			throw new Exception( 'Account info update failed. :(' );
		return true;
	}
}
