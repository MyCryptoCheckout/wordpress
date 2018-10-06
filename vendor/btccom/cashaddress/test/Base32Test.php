<?php

namespace Test\CashAddr;

use CashAddr\Base32;
use CashAddr\Exception\Base32Exception;
use CashAddr\Exception\InvalidChecksumException;

class Base32Test extends TestBase
{
    /**
     * @throws \CashAddr\Exception\Base32Exception
     * @param string $string
     * @param string $prefix
     * @param string $hex
     * @param array $words
     * @dataProvider getValidTestCase
     */
    public function testFromAndToWords($string, $prefix, $hex, array $words)
    {
        $binary = hex2bin($hex);
        $vBytes = array_values(unpack("C*", $binary));
        $numBytes = count($vBytes);
        $genWords = Base32::toWords($numBytes, $vBytes);
        $origBytes = Base32::fromWords(count($genWords), $words);
        $this->assertEquals($words, $genWords);
        $this->assertEquals($binary, pack("C*", ...$origBytes));
    }

    /**
     * @param string $string
     * @param string $prefix
     * @param string $hex
     * @param array $words
     * @throws \CashAddr\Exception\Base32Exception
     * @dataProvider getValidTestCase
     */
    public function testEncode($string, $prefix, $hex, array $words)
    {
        $encoded = Base32::encode($prefix, $words);
        $this->assertEquals($string, $encoded);

        list ($retPrefix, $retWords) = Base32::decode($string);
        $numWords = count($words);
        $bytes = Base32::fromWords($numWords, $words);

        $this->assertEquals($prefix, $retPrefix);
        $this->assertEquals($words, $retWords);
    }

    /**
     * @param string $string
     * @param string $prefix
     * @param string $hex
     * @param array $words
     * @throws \CashAddr\Exception\Base32Exception
     * @dataProvider getValidTestCase
     */
    public function testFailsForStringWith1BitFlipped($string, $prefix, $hex, array $words)
    {
        $sepIdx = strrpos($string, Base32::SEPARATOR);
        $this->assertNotEquals(-1, $sepIdx, "separator was not found in fixture");

        $vchArray = str_split($string, 1);
        $vchArray[$sepIdx + 1] = ord($vchArray[$sepIdx + 1]) ^ 1;
        $string = implode($vchArray);

        $this->expectException(InvalidChecksumException::class);

        Base32::decode($string);
    }

    /**
     * @param string $string
     * @param string $exception
     * @dataProvider getDecodeFailTestCase
     */
    public function testDecodeFails($string, $exception = "")
    {
        $this->expectException(Base32Exception::class);
        if ($exception !== "") {
            $this->expectExceptionMessage($exception);
        }

        Base32::decode($string);
    }
}
