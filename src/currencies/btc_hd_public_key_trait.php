<?php

namespace mycryptocheckout\currencies;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\GlobalPrefixConfig;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\NetworkConfig;
use BitWasp\Bitcoin\Network\Slip132\BitcoinRegistry;
use BitWasp\Bitcoin\Key\Deterministic\Slip132\Slip132;
use BitWasp\Bitcoin\Key\KeyToScript\KeyToScriptHelper;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\Base58ExtendedKeySerializer;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\ExtendedKeySerializer;
use Exception;

/**
	@brief		Trait to handle the generation of HD addresses.
	@since		2018-07-01 14:39:11
**/
trait btc_hd_public_key_trait
{

	/**
		@brief		Generate an ETH HD address.
		@since		2021-02-09 17:13:02
	**/
	public function eth_hd_public_key_generate_address( $wallet )
	{
		$address = $wallet->address;
		$public_key = $wallet->get( 'btc_hd_public_key' );

		$path_key = 'btc_hd_public_key_generate_address_path';
		$path_value = $wallet->get( $path_key, 0 );

		// xpub6EoaP1G4yqxsDmHKiiRjbs51NxTRcfqhuGNLrR9HgqR25YCbwNJogBjoqHJzKZMCY4hHfv81VLX3t8q9NpCUinQkz37AfWdSKNYdzjaK8cG
		// 0 0x128b25a4E357A5a8af033b5993510F0468846065
		// 1 0xc47882594082c3038835972bC41984Bb4918332e
		// 2 0x13D665B330916D1B56497202724C59C5cA7522F0

		$hd = new \phpEther\HD();
		$hd->publicSeed( $public_key );
		return $hd->getAddress( $path_value );
	}

	/**
		@brief		Generate a new HD address.
		@details	This code is adapted from Mario Dian's page on how to generate HD wallet addresses.
		@see		https://freedomnode.com/blog/58/generate-bitcoin-wallet-addresses-from-extended-public-key-with-php
		@since		2018-07-01 14:40:21
	**/
	public function btc_hd_public_key_generate_address( $wallet )
	{
		$address = $wallet->address;

		// The wallet must have a public key set.
		$public_key = $wallet->get( 'btc_hd_public_key' );
		if ( ! $public_key )
			return $address;

		// Handle ETH differently.
		$currencies = MyCryptoCheckout()->currencies();
		$currency = $currencies->get( $wallet->get_currency_id() );
		if ( $currency->id == 'ETH' )
		// if ( isset( $currency->erc20 ) || $currency->id == 'ETH' )
			return $this->eth_hd_public_key_generate_address( $wallet );

		$adapter = Bitcoin::getEcAdapter();
		$slip132 = new Slip132(new KeyToScriptHelper($adapter));

		switch( $wallet->get_currency_id() )
		{
			case 'BCH':
				$network = \Btccom\BitcoinCash\Network\NetworkFactory::bitcoinCash();
				$prefixes = new BitcoinRegistry();
			break;
			case 'DASH':
				$network = NetworkFactory::dash();
				$prefixes = new BitcoinRegistry();
			break;
			case 'DGB':
				$network = new DigibyteNetwork();
				$prefixes = new BitcoinRegistry();
			break;
			case 'GRS':
				$network = new GroestlNetwork();
				$prefixes = new BitcoinRegistry();
			break;
			case 'LTC':
				$network = NetworkFactory::litecoin();
				$prefixes = new BitcoinRegistry();
			break;
			case 'VIA':
				$network = new ViacoinNetwork();
				$prefixes = new BitcoinRegistry();
			break;
			default:
				$network = NetworkFactory::bitcoin();
				$prefixes = new BitcoinRegistry();
			break;
		}

		$pubPrefix = '';
		switch( substr( $public_key, 0, 4 ) )
		{
			case 'xpub':
				$pubPrefix = $slip132->p2pkh($prefixes);
			break;
			case 'ypub':
				$pubPrefix = $slip132->p2shP2wpkh($prefixes);
			break;
			case 'zpub':
				$pubPrefix = $slip132->p2wpkh($prefixes);
			break;
		}

		if ( $pubPrefix == '' )
			return $address;

		// Max is 2^31 - 1
		$path_key = 'btc_hd_public_key_generate_address_path';
		$path_value = $wallet->get( $path_key, 0 );
		$path = '0/' . $path_value;

		$config = new GlobalPrefixConfig( [
		  new NetworkConfig( $network, [
			$pubPrefix,
		  ] )
		] );

		switch( $wallet->get_currency_id() )
		{
			case 'GRS':
				// They're sorta using a base58. Almost.
				$serializer = new groestl\GroestlExtendedKeySerializer(
				  new ExtendedKeySerializer( $adapter, $config )
				);
			break;
			default:
				$serializer = new Base58ExtendedKeySerializer(
				  new ExtendedKeySerializer( $adapter, $config )
				);
				break;
		}

		$key = $serializer->parse( $network, $public_key );
		$child_key = $key->derivePath( $path );

		switch( $wallet->get_currency_id() )
		{
			case 'BCH':
				$address = $child_key->getAddress( new AddressCreator() )->getAddress();
				$dir = dirname( MyCryptoCheckout()->paths( '__FILE__' ) );
				$dir = $dir . '/src/thirdparty/CashAddress.php';
				require_once( $dir );
				$ca = new \CashAddress\CashAddress();
				$address = $ca->old2new( $address );
				$address = preg_replace( '/.*:/', '', $address );
			break;
			case 'BTC':
				$address = $child_key->getAddress( new AddressCreator() )->getAddress();
			break;
			case 'DASH':
			case 'DGB':
			case 'GRS':
			case 'LTC':
			case 'VIA':
				$address = $child_key->getAddress( new AddressCreator() )->getAddress( $network );
			break;
		}

		return $address;
	}

	/**
		@brief		Create a new address if necessary.
		@since		2018-07-01 14:39:46
	**/
	public function btc_hd_public_key_use_wallet( $action )
	{
		$new_address = $action->wallet->get_address();
		try
		{
			$new_address = $this->btc_hd_public_key_generate_address( $action->wallet );
		}
		catch ( Exception $e )
		{
		}
		$action->wallet->address = $new_address;

		// Increase the path value.
		$path_key = 'btc_hd_public_key_generate_address_path';
		$path_value = $action->wallet->get( $path_key, 0 );
		$path_value++;
		$action->wallet->set( $path_key, $path_value );
	}
}
