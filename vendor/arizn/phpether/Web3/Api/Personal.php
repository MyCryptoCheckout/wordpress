<?php

namespace phpEther\Web3\Api;

use phpEther\Web3;
use phpEther\Web3\Providers\Provider;
use Graze\GuzzleHttp\JsonRpc\Message\ResponseInterface;
use phpseclib\Math\BigInteger;

class Personal implements Api
{

    protected $web3;

    protected $provider;

    /**
     * Personal constructor.
     * @param Web3 $web3
     * @param Provider $provider
     */
    public function __construct(Web3 $web3, Provider $provider)
    {
        $this->web3 = $web3;
        $this->provider = $provider;
    }

    /**
     * @param string $password
     * @return string
     */
    public function newAccount(string $password) : string
    {
		$module = $this->provider->getPersonal();
        return $module->personal_newAccount($password);
    }

    /**
     * @param string $address
     * @param string $passPhrase
     * @param int $duration
     * @return mixed
     */
    public function unlockAccount(\phpEther\Account $account, int $duration = 0)
    {
		$module = $this->provider->getPersonal();
        return $module->personal_unlockAccount($account->address, $account->password, $this->web3->toHex($duration));
    }
	
	public function sendTransaction(\phpEther\Transaction $tx, string $password) : string
    {
		$module = $this->provider->getPersonal();
        return $module->personal_sendTransaction($tx->getArray(), $password);
    }
	
}