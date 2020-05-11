<?php

namespace mycryptocheckout\cli;

use Exception;
use WP_CLI;

/**
	@brief		Dump public addresses
	@since		2020-04-24 21:45:26
**/
class Dump_Pub
{
	/**
		@brief		The MCC CLI class.
		@since		2019-01-09 14:30:25
	**/
	public $cli;

	/**
		@brief		Constructor.
		@since		2019-01-09 14:29:54
	**/
	public function __construct( $cli )
	{
		$this->cli = $cli;
	}

	/**
		@brief		Run all of the internal tests.
		@since		2019-01-09 14:28:56
	**/
	public function run( $args )
	{
		$currency_id = $args[ 0 ];
		$pub = $args[ 1 ];
		$currencies = MyCryptoCheckout()->currencies();
		$wallets = MyCryptoCheckout()->wallets();
		$currency = $currencies->get( $currency_id );

		$small_pub = substr( $pub, 0, 4 );
		$wallet = $wallets->new_wallet();
		$wallet->address = 'x';
		$wallet->currency_id = $currency_id;
		$wallet->set( 'btc_hd_public_key', $pub );

		for( $index = 0; $index <= 1024; $index ++ )
		{
			$wallet->set( 'btc_hd_public_key_generate_address_path', $index );
			$new_address = $currency->btc_hd_public_key_generate_address( $wallet );
			WP_CLI::line( sprintf( 'Index %s: %s', $index, $new_address ) );
		}
	}
}
