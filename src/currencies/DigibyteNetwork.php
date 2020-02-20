<?php

namespace mycryptocheckout\currencies;

/**
Public key for tests:
xpub69HeFrui51qASN5oK1rnzMBacAM7go69u7d1oZq4MRYZmwzHFKT283yQCoYGZAgqqQEPfnLbyCjJaDhFp5CG6b93rQV7dDu26yTrj25Uei4

Addresses:
D6fuyNgwuGXzUw9ujQ2kh5WsP3uPtJghFF
DDc2Ku8G17Ae7awHAU6iPRudnVprKKPwfg
DJmMMgBdc1WWDthsUvpHkzRQosuqAzYh8a
DEW7aHD7toLnazs8BS5wJwXHBufWrx2qzQ
DLT5WiL4aoBigbeBSLhcKEpc4bSwFGcEv1
*/

use BitWasp\Bitcoin\Network\Network;
use BitWasp\Bitcoin\Script\ScriptType;
class DigibyteNetwork extends Network
{
    /**
     * {@inheritdoc}
     * @see Network::$base58PrefixMap
     */
    protected $base58PrefixMap = [
		// https://github.com/SmartCash/Core-Smart/blob/master/src/chainparams.cpp#L165 - L167 on 5/5/19. Convert numbers from decimal to hex.
        self::BASE58_ADDRESS_P2PKH => "1E",
        self::BASE58_ADDRESS_P2SH => "3F",
        self::BASE58_WIF => "80",
    ];

    /**
     * {@inheritdoc}
     * @see Network::$bech32PrefixMap
     */
    protected $bech32PrefixMap = [
        self::BECH32_PREFIX_SEGWIT => "dgb",
    ];

    /**
     * {@inheritdoc}
     * @see Network::$bip32PrefixMap
     */
    protected $bip32PrefixMap = [
		// https://github.com/SmartCash/Core-Smart/blob/master/src/chainparams.cpp#L168 - L169 on 5/5/19. Drop 0x's and concatenate the numbers.
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
    protected $signedMessagePrefix = "Digibyte Signed Message";
    /**
     * {@inheritdoc}
     * @see Network::$p2pMagic
     */
	 // https://github.com/SmartCash/Core-Smart/blob/master/src/chainparams.cpp#L136 - L139 on 5/5/19. Drop 0x's and concatenate in reverse (ascending) order.
	protected $p2pMagic = "dab6c3fa";
}
