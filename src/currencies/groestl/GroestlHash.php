<?php

namespace mycryptocheckout\currencies\groestl;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
	@brief		Basically base58, but without the checksum.
	@since		2019-01-17 22:50:50
**/
class GroestlHash
	extends \BitWasp\Bitcoin\Base58
{
    /**
     * Decode a base58 checksum string and validate checksum
     *
     * @param string $base58
     * @return BufferInterface
     * @throws Base58ChecksumFailure
     */
    public static function decodeCheck($base58)
    {
        $hex = self::decode($base58);
        $data = $hex->slice(0, -4);
        return $data;
    }
}
