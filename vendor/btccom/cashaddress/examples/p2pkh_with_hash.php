<?php

require __DIR__ . "/../vendor/autoload.php";

use CashAddr\CashAddress;

$prefix = "bitcoincash";
$keyHashHex = "205ced081985f683aaea817ea1b5263095809152";

if ($argc > 1) {
    $prefix = $argv[1];
}

if ($argc > 2) {
    $keyHashHex = $argv[2];
}

$keyHash = pack("H*", $keyHashHex);
$address = CashAddress::pubKeyHash($prefix, $keyHash);
echo $address . PHP_EOL;
