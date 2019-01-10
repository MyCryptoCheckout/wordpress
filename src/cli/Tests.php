<?php

namespace mycryptocheckout\cli;

use Exception;
use WP_CLI;

/**
	@brief		The testsuite for MCC.
	@since		2019-01-09 14:29:04
**/
class Tests
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
	public function run()
	{
		// Test account.
		$this->cli->update_account();

		$this->test_wallets();
		$this->test_hd_wallets();
	}

	/**
		@brief		Test the HD wallets.
		@since		2019-01-10 22:08:47
	**/
	public function test_hd_wallets()
	{
		$currencies = MyCryptoCheckout()->currencies();
		$wallets = MyCryptoCheckout()->wallets();

		foreach( [
			'BTC' => [
				'xpub661MyMwAqRbcGkCtDgveovuTzXX4Jnf3ja6kS5iN1ha3mNKaspHRJYzrGkbz6HLsPRJVd7iq9utnE7zBPbYtBRuQN79MLVJumLUwZaToKWX' => [
					0 => '1F9c7oEazFLx5xyA38zhad1jgw7PHcYcJQ',
					1 => '1PnyjDv7hSZWXLsZA8K7J7VyEf5XQK5Lj8',
					2 => '1D3bnYGXdN5oV46Rdayshxq8ortJ6P38nX',
				],
			],
			'LTC' => [
				'xpub661MyMwAqRbcEtLWbRkdoi4iyDKyCZDaXAi5KjUq6qGUmrTMSkhSGmGSsTjXxAqPUoK7bH9Btj4NP6QdX7zKTzGixhBZu3iUXsEtQD3SbKq' => [
					0 => 'LNYKNpmGQp2hRxiSHdpxACBunZkP21HsTX',
					2 => 'LR2i7aMxx6XpC5efwnuje8qQ3o3fiqUaLb',
				],
				'zpub6o2iJcxFz9disvFRhTVoL3UQqxeMuq9AofPP5EpBPByY792uy1C1nZE1zHzGbacyB1icY7LoJsiQ3rNr5qEG6TY1aLjLLvbNf2sVXG7HQ3n' => [
					0 => 'ltc1qtyjz8kxyffnh46zkytc8zzj3eknnf4syycsvuj',
					1 => 'ltc1qgzrmdajg30us4tzufwcp9znu0evy27jd2z7jhr',
					2 => 'ltc1qza0zyp2ephz82wpc34lmzsqm0ydldd9suts74q',
				],
			],
			'BCH' => [
				'xpub661MyMwAqRbcF9oUAF1k4RFVbUFWud32pX1rf1QsyHRdcYTnZdoZLand63MMtajoKKMUVRJXDCn7Yj61GCFnuqEexxYzhjwpE4PvWHSwVqK' =>
				[
					0 => 'qpvs6t7cghxpvrgf27043awnvs907rl255sg3ewnv8',
					3 => 'qzmjxrsz42k6jau007gayvskk2trqwy26y06ayfa7k',
				],
			],
		] as $currency_id => $pubs )
		{
			$currency = $currencies->get( $currency_id );
			foreach( $pubs as $pub => $addresses )
			{
				$wallet = $wallets->new_wallet();
				$wallet->address = 'x';
				$wallet->currency_id = $currency_id;
				$wallet->set( 'btc_hd_public_key', $pub );
				foreach( $addresses as $index => $address )
				{
					$wallet->set( 'btc_hd_public_key_generate_address_path', $index );
					$new_address = $currency->btc_hd_public_key_generate_address( $wallet );
					if ( $new_address != $address )
						throw new Exception( sprintf( 'FAIL: %s HD wallet %s is bad: %s not %s', $currency_id, $index, $new_address, $address ) );
					WP_CLI::line( sprintf( 'PASS: %s HD wallet %s is %s', $currency_id, $index, $new_address ) );
				}
			}
		}
	}

	/**
		@brief		Test wallets.
		@since		2019-01-09 14:32:46
	**/
	public function test_wallets()
	{
		$currencies = MyCryptoCheckout()->currencies();
		$wallets = MyCryptoCheckout()->wallets();

		foreach( [
			[
				'bad_addresses' => [
					'3sTvR3RUZAdpzRfeK3q59HcbSD9hcFUA',	// Too short.
				],
				'currency_id' => 'BTC',
				'good_addresses' => [
					'13sTvR3RUZAdp4zRfeK3q59HcbSD9hcFUA',			// 1 and 3 are common.
					'bc1qtc4geels0j9dfs5te0huf63nf3r2we0z4u4f3n',	// Segwit
				],
			],
		] as $currency_to_check )
		{
			$currency_to_check = (object) $currency_to_check;
			$currency = $currencies->get( $currency_to_check->currency_id );
			foreach( $currency_to_check->good_addresses as $address )
			{
				$currency->validate_address( $address );
				$wallet = $wallets->new_wallet();
				$wallet->address = $address;
				$wallet->currency_id = $currency_to_check->currency_id;
				$index = $wallets->add( $wallet );
				WP_CLI::line( sprintf( 'PASS: Checking good address %s %s', $currency_to_check->currency_id, $address ) );
			}

			foreach( $currency_to_check->bad_addresses as $address )
			{
				try
				{
					$currency->validate_address( $address );
					WP_CLI::line( sprintf( 'FAIL: Checking %s %s', $currency_to_check->currency_id, $address ) );
					exit;
				}
				catch ( Exception $e )
				{
					// Expected fail, which is a pass.
					WP_CLI::line( sprintf( 'PASS: Checking bad address %s %s', $currency_to_check->currency_id, $address ) );
				}
			}
		}
	}
}
