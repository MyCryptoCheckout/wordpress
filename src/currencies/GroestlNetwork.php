<?php

namespace mycryptocheckout\currencies;

use BitWasp\Bitcoin\Network\Network;
use BitWasp\Bitcoin\Script\ScriptType;

/**
	@brief		Settings for Groestl network.
	@since		2019-01-17 20:29:12
**/
class GroestlNetwork extends Network
{
    /**
     * {@inheritdoc}
     * @see Network::$base58PrefixMap
     */
    protected $base58PrefixMap = [
        self::BASE58_ADDRESS_P2PKH => "24",
        self::BASE58_ADDRESS_P2SH => "05",
        self::BASE58_WIF => "80",

    ];

    /**
     * {@inheritdoc}
     * @see Network::$bech32PrefixMap
     */
    protected $bech32PrefixMap = [
        self::BECH32_PREFIX_SEGWIT => "grs",
    ];

    /**
     * {@inheritdoc}
     * @see Network::$bip32PrefixMap
     */
    protected $bip32PrefixMap = [
        self::BIP32_PREFIX_XPUB => "0488b21e",
        self::BIP32_PREFIX_XPRV => "0488ade4",
    ];

    /**
     * {@inheritdoc}
     * @see Network::$bip32ScriptTypeMap
     */
    protected $bip32ScriptTypeMap = [
        self::BIP32_PREFIX_XPUB => ScriptType::P2PKH,
        self::BIP32_PREFIX_XPRV => ScriptType::P2PKH,
    ];

    /**
     * {@inheritdoc}
     * @see Network::$p2pMagic
     */
    protected $p2pMagic = "f9beb4d4";
}
