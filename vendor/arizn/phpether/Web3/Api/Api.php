<?php

namespace phpEther\Web3\Api;

use phpEther\Web3;
use phpEther\Web3\Providers\Provider;

interface Api
{
    public function __construct(Web3 $web3, Provider $provider);
}