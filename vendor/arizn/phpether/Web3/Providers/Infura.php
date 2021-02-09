<?php
namespace phpEther\Web3\Providers;

use Graze\GuzzleHttp\JsonRpc\Client;
use phpEther\Web3\Providers\Geth;
class Infura extends Geth implements Provider 
{
    protected $client;
    protected $id = 0;
	const API_URL = "https://mainnet.infura.io/";
	const TESTNET_ROPSTEN = "ropsten";
	const TESTNET_MORDEN = "mordern";
    const TESTNET_KOVAN = "kovan";
    const TESTNET_RINKEBY = "rinkeby";
	const MAINNET = "mainnet";
   
	public function __construct(string $apiKeyToken , string $net ) {
        if (is_null($apiKeyToken)) {
            return;
        }
		$this->net = $net;
        $this->apiKeyToken = $apiKeyToken;
		parent::__construct($this->getAPIUrl(),$net);
        
    }
	
	public function getPersonal(){
		throw new Exception ('Infura.io API doesnt support the personal Module');
	}
	
	public  function getAPIUrl() {
        if (is_null($this->net)) {
            return self::API_URL.$this->apiKeyToken;
        }
        return "https://{$this->net}.infura.io/".$this->apiKeyToken;
    }
	
	function eth_sendTransaction(\phpEther\Transaction $transaction)
	{
		return parent::geth_request(__FUNCTION__, $transaction->toArray(true));	
	}
	
	function eth_call(\phpEther\Transaction $message, $block = self::TAG_LATEST)
	{
		return parent::geth_request(__FUNCTION__, [$message->getArray(true) , $block]);
	}
	
	function eth_estimateGas(\phpEther\Transaction $message, $block = self::TAG_LATEST)
	{
		return parent::geth_request(__FUNCTION__, [$message->getArray(true)]);
	}

	
	function eth_newFilter(\phpEther\Filter $filter, $decode_hex=FALSE)
	{
		$id = parent::geth_request(__FUNCTION__, $filter->toArray());
		if($decode_hex)
			$id = $this->decode_hex($id);
		return $id;
	}
}