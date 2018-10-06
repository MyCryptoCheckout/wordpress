<?php

namespace Test\CashAddr;

class TestBase extends \PHPUnit_Framework_TestCase
{
    public function readTest()
    {
        $decoded = json_decode(file_get_contents(__DIR__ . "/fixtures.json"), true);
        if (false === $decoded) {
            throw new \RuntimeException("Invalid json in test fixture");
        }

        return $decoded;
    }

    public function getValidTestCase()
    {
        $fixtures = [];
        foreach ($this->readTest()['valid'] as $valid) {
            $fixtures[] = [$valid['string'], $valid['prefix'], $valid['hex'], $valid['words'], $valid['scriptType']];
        }

        return $fixtures;
    }

    public function getDecodeFailTestCase()
    {
        $fixtures = [];
        foreach ($this->readTest()['invalid'] as $invalid) {
            if (!array_key_exists('string', $invalid)) {
                continue;
            }
            $fixtures[] = [$invalid['string'], $invalid['exception'],];
        }

        return $fixtures;
    }
}
