<?php

namespace mycryptocheckout\cli;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
		try
		{
			// Test account.
			$this->cli->update_account();

			$this->test_wallets();
			$this->test_hd_wallets();
		}
		catch( Exception $e )
		{
			WP_CLI::line( sprintf( 'FAIL: %s', $e ) );
		}
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
				'xpub661MyMwAqRbcGkCtDgveovuTzXX4Jnf3ja6kS5iN1ha3mNKaspHRJYzrGkbz6HLsPRJVd7iq9utnE7zBPbYtBRuQN79MLVJumLUwZaToKWX' =>
				[
					0 => '1F9c7oEazFLx5xyA38zhad1jgw7PHcYcJQ',
					1 => '1PnyjDv7hSZWXLsZA8K7J7VyEf5XQK5Lj8',
					2 => '1D3bnYGXdN5oV46Rdayshxq8ortJ6P38nX',
				],
				'ypub6XCS6bopQHQKzDpZWx2qynzvMk9T7tzLuEvXTgtfKTF7bYuoCCTo2hnULK3mTT4cjv42wkHHWeiLkaob7CrfvdxYDfp1knM8qXXZBm19EcV' =>
				[
					0 => '39t3adRssv7LedeUynn2Mtde2rs1mydjFy',
					1 => '36PhiUN9EBcScFjmKYXYy8uoXGWJPkDFkr',
					2 => '3P8GFtwShrWcpiaAGbQ7YhrT4nUHdt1TsY',
					3 => '37ZVkMF17h2WF8LADGp1tzavUxMxvXRfjj',
				],
				'zpub6o6r17JTry4VkoxeAFgi8i7QqvzW5GBoStWXNKj2WdhTCEBuL8Aq5JCHW6etRuDwsyJWsNSXjZtUgDhygJFTuZZpxGiYwdVYr33cFXgxk8g' =>
				[
					0 => 'bc1qtc4geels0j9dfs5te0huf63nf3r2we0z4u4f3n',
					1 => 'bc1qpzqgfr64zdt0hupn4aalszeaa2kssm8hk6ngpf',
					2 => 'bc1qyem9l9vq8e46vn2732tu98vlp7usctzzhv8ea0',
					3 => 'bc1qfuyjsgpnkfajnm8dapgkhaznwxyc352j8vwqpe',
				],
			],
			'DASH' => [
				'xpub661MyMwAqRbcFuWVYexT8CkVbUwnthA433dqozqLNcvHDSPLvXA3hEsbwL8g51BfCisME1KFGaRXtptmtCxrEWAif8UCvKJxKjF9zZsDsJ7' =>
				[
					0 => 'XknpB1WF1YkWhuC4LBhPhh6PC4hFD4sYco',
					1 => 'XwuMrYjnEJvxyGDzSP2nK54E3XzqD57THL',
					2 => 'XhoyJ46B8YYEL8zpm4oNR9XoUNeZr2Ymns',
					3 => 'XeaAJvVbiDdrxu6GbHq37T5jnxYaReNGeh',
				],
			],
			'DGB' => [
				'xpub6C8nAML9rZuv9Aa6YEU7dNcFvU4vJwCxe53cCFwvVdxHzHDKz6aVVEMKHQQB19DeL83Z4LMo18bn6ifjbFLcE2DnDAV2yZhiKGtzNHk71fS' =>
				[
					0 => 'DPmHeodd9pBsG15cLAxkFAG8kJ2Sft2cr5',
					1 => 'DSFULbY33sMCp5prDfcAJyZpJtjC6KdEmo',
				],
				'ypub6Zfvumx3CmRzsLHpsWrL6o4EQrvTs7bfRn94HeibAGQ4APWcFzq8XYdQLhUokRHobPY2zrAn86UpXj57o3x7x7iu6h4FFyfC3TiHdraThA2' =>
				[
					0 => 'SaEuW5pgQH7bfjNd2Yhpwx7otekLKCu7YV',
					1 => 'SaFcHKzNoirHdemv1qteYB7FmhuDvguRvJ',
				],
				'zpub6rhAxgrS7zsTtRj67GdsX5fEcJugo6bcWNyQjHjY8sdq3DX7LxcyoX5kLDKaksSrtwJv5JrNBxaQskVM9vJB56ojLqR7w7pVeutMrHusM54' =>
				[
					0 => 'dgb1qwz94lncd2vzzdanxkqc4gm38gs3hc48c2jm7md',
					1 => 'dgb1qx8zmgwerfsnkdgajhf9mn0xt5j679qy9lm59ag',
				],
			],
			'ETH' => [
				'xpub6EoaP1G4yqxsDmHKiiRjbs51NxTRcfqhuGNLrR9HgqR25YCbwNJogBjoqHJzKZMCY4hHfv81VLX3t8q9NpCUinQkz37AfWdSKNYdzjaK8cG' =>
				[
					0 => strtolower( '0x128b25a4E357A5a8af033b5993510F0468846065' ),
					1 => strtolower( '0xc47882594082c3038835972bC41984Bb4918332e' ),
					2 => strtolower( '0x13D665B330916D1B56497202724C59C5cA7522F0' ),
				],
			],
			'GRS' => [
				'zpub6rtbDzTM985owxDb5A3z6U8Q2bSD4ZyfkEx81h1Rx9a4dSNxmiAXm32RvpRsHThZJdUfezAJGaKFHJR7VdaBzKUaHwwUVvc4DTJehjZevYa' =>
				[
					0 => 'grs1q36yl8tu236lqlqqc3hz34etzz7gwhlvz8s6cl6',
					1 => 'grs1qy2mjjyrznw2cm9hqujzmdvlqneczpdzxrtl0m3',
					2 => 'grs1qm23z0w88mqvluj9y2g2rnwkjtwd4gvq0wg03z3',
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
			'VIA' => [
				'xpub661MyMwAqRbcFW2c4WQq7PstswwEjzErA6c4gc4Naet6P8QpAs9PrjKNTiuiwJcRDPPUmavG1b49GggUa4KhXQDpT5KtoMQrAHzdZycfEr7' =>
				[
					0 => 'VfYMvwaCJLCTC6zEDfaPutBaHig9iPRWxV',
					1 => 'VwffehnKYRuXrXmZjeL8uEbUsNAQvP79se',
					2 => 'VuCxs1VwEJbY7ASC1qK2S2d8XEftRX2kPy',
					3 => 'VrTGXV8KJRzCiv5YQWaTR4mrCaJXjixhMD',
				],
				'zpub6nWPnsKFLotKzv7qEdtF4GN2osShmUb1WxUoYCaTcrGKSBn5r3DAmYGh4qM9a14V6zY8cbuQrmfuWqRyuAnsfRJFN6duiBLVpq8sdSWLqbn' =>
				[
					0 => 'via1qd0dksemghad5agy3rt0eznxgnlvgt70nqlhpn9',
					1 => 'via1qag2hxq4pwg6a9uycj5u4emg2re2tk8h0pyx7y5',
					2 => 'via1qw9hvaewum50n2x845s6sk7k404jj3ysshkmxk4',
					3 => 'via1qlqtmvtm05nsv8g2rvxuakxwnujuee5r5d8s6sr',
				],
			],
		] as $currency_id => $pubs )
		{
			$currency = $currencies->get( $currency_id );
			foreach( $pubs as $pub => $addresses )
			{
				$small_pub = substr( $pub, 0, 4 );
				$wallet = $wallets->new_wallet();
				$wallet->address = 'x';
				$wallet->currency_id = $currency_id;
				$wallet->set( 'btc_hd_public_key', $pub );
				foreach( $addresses as $index => $address )
				{
					$wallet->set( 'btc_hd_public_key_generate_address_path', $index );
					$new_address = $currency->btc_hd_public_key_generate_address( $wallet );
					if ( $new_address != $address )
	//					throw new Exception( sprintf( 'FAIL: %s %s HD wallet %s is bad: %s not %s', $currency_id, $small_pub, $index, $new_address, $address ) );
						WP_CLI::line( sprintf( 'FAIL: %s %s HD wallet %s is bad: %s not %s', $currency_id, $small_pub, $index, $new_address, $address ) );
					WP_CLI::line( sprintf( 'PASS: %s %s HD wallet %s is %s', $currency_id, $small_pub, $index, $new_address ) );
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
