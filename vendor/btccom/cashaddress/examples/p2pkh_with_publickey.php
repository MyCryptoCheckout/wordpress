<?php

require __DIR__ . "/../vendor/autoload.php";

use CashAddr\CashAddress;

$prefix = "bitcoincash";
$publicKeyHex = "0242f9a1c88b6918b64e43e0de371f2087196117f89f7f2cc438e9fe7a1f6f041a";

if ($argc > 1) {
    $prefix = $argv[1];
}

if ($argc > 2) {
    $publicKeyHex = $argv[2];
}

$publicKey = pack("H*", $publicKeyHex);

$address = CashAddress::pubKeyHashFromKey($prefix, $publicKey);
echo $address . PHP_EOL;
