<?php
namespace phpEther\Web3\Providers;

interface Provider
{
    /**
     * @param string $method
     * @param null|array $params
     * @return mixed
     */
    public function request($method, $params = null);
}