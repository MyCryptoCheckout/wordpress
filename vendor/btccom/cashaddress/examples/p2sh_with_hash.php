<?php

require __DIR__ . "/../vendor/autoload.php";

use CashAddr\CashAddress;

$prefix = "bitcoincash";
$scriptHashHex = "205ced081985f683aaea817ea1b5263095809152";

if ($argc > 1) {
    $prefix = $argv[1];
}

if ($argc > 2) {
    $scriptHashHex = $argv[2];
}

$scriptHash = pack("H*", $scriptHashHex);
$address = CashAddress::scriptHash($prefix, $scriptHash);
echo $address . PHP_EOL;
