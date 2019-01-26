<?php

namespace mycryptocheckout\currencies\groestl;

use BitWasp\Bitcoin\Key\Deterministic\HierarchicalKey;
use BitWasp\Bitcoin\Network\NetworkInterface;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\ExtendedKeySerializer;

/**
	@brief		A serializer that doesn't use base58.
	@since		2019-01-17 20:52:53
**/
class GroestlExtendedKeySerializer
	extends \BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\Base58ExtendedKeySerializer
{
    /**
     * @var ExtendedKeySerializer
     */
    private $serializer;

    /**
     * @param ExtendedKeySerializer $hdSerializer
     */
    public function __construct(ExtendedKeySerializer $hdSerializer)
    {
        $this->serializer = $hdSerializer;
    }

    /**
     * @param NetworkInterface $network
     * @param HierarchicalKey $key
     * @return string
     */
    public function serialize(NetworkInterface $network, HierarchicalKey $key)
    {
        return GroestlHash::encodeCheck($this->serializer->serialize($network, $key));
    }

    /**
     * @param NetworkInterface $network
     * @param string $base58
     * @return HierarchicalKey
     */
    public function parse(NetworkInterface $network, $base58)
    {
        return $this->serializer->parse($network, GroestlHash::decodeCheck($base58));
    }
}
