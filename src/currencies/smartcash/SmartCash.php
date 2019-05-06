<?php

namespace mycryptocheckout\currencies\smartcash;

/**
Public key for tests:
xpub661MyMwAqRbcGej6Edrjf67WGrZhqEuwjFRrnGRPitSqaBDhBbYxAZ2zMDC67NSKGLEKNS4aPoxpFBXHd1f6dqpaV3UPoWPL2yAgapTgDRo

Addresses:
SXLWPFb9pUscK21MiwAZk3bfebyWW6biaa
SkE18TTVLJHvGEegfoXDE8kvUxjqWPnp6Q
SVhDvyYt4FXtGonuCDVVUc4BRvmqqrQFEx
SPXwwHzyex7BdzAN7u2uKjmJqG9KfNxwZG
*/

use BitWasp\Bitcoin\Network\Network;
use BitWasp\Bitcoin\Script\ScriptType;

class SmartCash extends Network
{
    /**
     * {@inheritdoc}
     * @see Network::$base58PrefixMap
     */
    protected $base58PrefixMap = [
        self::BASE58_ADDRESS_P2PKH => "3f",
        self::BASE58_ADDRESS_P2SH => "12",
        self::BASE58_WIF => "bf",
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
     * @see Network::$signedMessagePrefix
     */
    protected $signedMessagePrefix = "SmartCash Signed Message";
    /**
     * {@inheritdoc}
     * @see Network::$p2pMagic
     */
    protected $p2pMagic = "1eaba15c";
}
