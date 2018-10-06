<?php

namespace Test\CashAddr;

use CashAddr\Base32;
use CashAddr\CashAddress;
use CashAddr\Exception\CashAddressException;

class CashAddressTest extends TestBase
{
    /**
     * @param $string
     * @param $prefix
     * @param $hex
     * @param array $words
     * @throws \CashAddr\Exception\Base32Exception
     * @throws \CashAddr\Exception\CashAddressException
     * @dataProvider getValidTestCase
     */
    public function testCashAddress($string, $prefix, $hex, array $words, $scriptType)
    {
        list ($retPrefix, $retScriptType, $retHash) = CashAddress::decode($string);
        $this->assertEquals($prefix, $retPrefix);
        $this->assertEquals($scriptType, $retScriptType);

        if ($scriptType === "scripthash") {
            $this->assertEquals(20, strlen($retHash));
        } else if ($scriptType === "pubkeyhash") {
            $this->assertEquals(20, strlen($retHash));
        }

        $rebuildPayload = unpack("H*", pack("C*", ...Base32::fromWords(count($words), $words)))[1];
        $this->assertEquals($hex, $rebuildPayload);

        $encodeAgain = CashAddress::encode($retPrefix, $retScriptType, $retHash);
        $this->assertEquals($string, $encodeAgain);
    }

    public function testHelpersPubKeyHash()
    {
        $publicKey = hex2bin("0242f9a1c88b6918b64e43e0de371f2087196117f89f7f2cc438e9fe7a1f6f041a");
        $publicKeyHash = hex2bin("205ced081985f683aaea817ea1b5263095809152");
        $expectedAddress = "bitcoincash:qqs9emggrxzldqa2a2qhagd4yccftqy32gd78q2wjr";

        $this->assertEquals($publicKeyHash, hash('ripemd160', hash('sha256', $publicKey, true), true));

        $addressPubKey = CashAddress::pubKeyHashFromKey("bitcoincash", $publicKey);
        $addressPubKeyHash = CashAddress::pubKeyHash("bitcoincash", $publicKeyHash);

        $this->assertSame($expectedAddress, $addressPubKeyHash);
        $this->assertSame($expectedAddress, $addressPubKey);
    }

    public function testHelpersScriptHash()
    {
        $scriptHash = hex2bin("205ced081985f683aaea817ea1b5263095809152");
        $expectedAddress = "bitcoincash:pqs9emggrxzldqa2a2qhagd4yccftqy32g6m60ddf7";

        $addressScriptHash = CashAddress::scriptHash("bitcoincash", $scriptHash);

        $this->assertSame($expectedAddress, $addressScriptHash);
    }

    public function getInvalidPublicKeys()
    {
        return [
            // missing last byte
            ["046aab2fac8e5967279b6bb5390c970fc0a91281e75dfadf8b887b46b0bd8b3d76bc4c40dfc9c33099db14916c507cef54f2aab2a731ac7450b970789de40a03"],
            // invalid prefix
            ["036aab2fac8e5967279b6bb5390c970fc0a91281e75dfadf8b887b46b0bd8b3d76bc4c40dfc9c33099db14916c507cef54f2aab2a731ac7450b970789de40a0329"],
            ["026aab2fac8e5967279b6bb5390c970fc0a91281e75dfadf8b887b46b0bd8b3d76bc4c40dfc9c33099db14916c507cef54f2aab2a731ac7450b970789de40a0329"],
            ["0442f9a1c88b6918b64e43e0de371f2087196117f89f7f2cc438e9fe7a1f6f041a"],
            // Missing last byte
            ["0342f9a1c88b6918b64e43e0de371f2087196117f89f7f2cc438e9fe7a1f6f04"],
            ["0242f9a1c88b6918b64e43e0de371f2087196117f89f7f2cc438e9fe7a1f6f04"],
        ];
    }

    /**
     * @dataProvider getInvalidPublicKeys
     * @param string $publicKey
     * @throws CashAddressException
     * @throws \CashAddr\Exception\Base32Exception
     */
    public function testRejectsInvalidPublicKeys($publicKey)
    {
        $this->expectExceptionMessage(CashAddressException::class);
        $this->expectExceptionMessage("Invalid public key");

        CashAddress::pubKeyHashFromKey("bitcoincash", hex2bin($publicKey));
    }
}
