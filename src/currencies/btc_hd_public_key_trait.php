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

/**
	@brief		Trait to handle the generation of HD addresses.
	@since		2018-07-01 14:39:11
**/
trait btc_hd_public_key_trait
{
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

		$adapter = Bitcoin::getEcAdapter();
		$slip132 = new Slip132(new KeyToScriptHelper($adapter));
		$bitcoin_prefixes = new BitcoinRegistry();

		$pubPrefix = '';
		switch( substr( $public_key, 0, 4 ) )
		{
			case 'xpub':
				$pubPrefix = $slip132->p2pkh($bitcoin_prefixes);
			break;
			case 'ypub':
				$pubPrefix = $slip132->p2shP2wpkh($bitcoin_prefixes);
			break;
			case 'zpub':
				$pubPrefix = $slip132->p2wpkh($bitcoin_prefixes);
			break;
		}

		if ( $pubPrefix == '' )
			return $address;

		// Max is 2^31 - 1
		$path = '0/' . rand( 1, 2147483647 );

		$network = NetworkFactory::bitcoin();

		$config = new GlobalPrefixConfig( [
		  new NetworkConfig( $network, [
			$pubPrefix,
		  ] )
		] );

		$serializer = new Base58ExtendedKeySerializer(
		  new ExtendedKeySerializer($adapter, $config)
		);

		$key = $serializer->parse( $network, $public_key );
		$child_key = $key->derivePath( $path );

		$address = $child_key->getAddress( new AddressCreator() )->getAddress();
		return $address;
	}

	/**
		@brief		Create a new address if necessary.
		@since		2018-07-01 14:39:46
	**/
	public function btc_hd_public_key_use_wallet( $action )
	{
		$new_address = $this->btc_hd_public_key_generate_address( $action->wallet );
		ddd( '%s %s', $action->wallet->address, $new_address );
		$action->wallet->address = $new_address;
	}
}
