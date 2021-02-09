<?php

namespace phpEther\Web3\Api;
use BitWasp\Buffertools\Buffer;
use phpEther\Web3;
use phpEther\Tools\Hex;
use phpEther\Web3\Providers\Provider;
use Graze\GuzzleHttp\JsonRpc\Message\ResponseInterface;
use phpEther\Web3\Api\Eth\Contract;

class Eth implements Api
{

    public $web3;

    public $provider;

    public $defaultAccount;

    public $defaultBlock = self::DEFAULT_BLOCK_LATEST;

    CONST DEFAULT_BLOCK_EARLIEST = "earliest";
    CONST DEFAULT_BLOCK_LATEST = "latest";
    CONST DEFAULT_BLOCK_PENDING = "pending";

    /**
     * Eth constructor.
     * @param Web3 $web3
     * @param Provider $provider
     */
    public function __construct(Web3 $web3, Provider $provider)
    {
        $this->web3 = $web3;
        $this->provider = $provider;
    }
	
    
    /**
     * @param $addressHexString
     * @param string $defaultBlock
     * @return BigInteger
     */
    public function getBalance($addressHexString, $defaultBlock = "latest") :Buffer
    {
        $balance = $this->provider->eth_getBalance($addressHexString, $defaultBlock);
        return \phpEther\Tools\Hex::buffer($balance);
    }

    /**
     * @param array $object
     * @return string
     */
    public function sendTransaction(\phpEther\Transaction $tx) : string
    {
        return $this->provider->eth_sendTransaction($tx);
    }
	
	public function signTransaction(\phpEther\Transaction $tx) : string
    {
        return $this->provider->eth_signTransaction($tx);
    }
	
	/**
     * @param array $object
     * @return string
     */
    public function sendRawTransaction($hex) : string
    {
        return $this->provider->eth_sendRawTransaction($hex);
    }

    /**
     * @param array $abi
     * @return Contract
     */
    public function contract($abi)
    {
        return new Contract($this, $abi);
    }

    /**
     * @return BigInteger
     */
    public function blockNumber():Buffer
    {
        $blockNumber = $this->provider->eth_blockNumber([]);
        return \phpEther\Tools\Hex::buffer($blockNumber);
    }

	public function getTransactionCount($addressHexString):Buffer {
        $response = $this->provider->eth_getTransactionCount($addressHexString);
		return \phpEther\Tools\Hex::buffer( $response );;
    }
	
	public function gasPrice():Buffer {
        $response =  $this->provider->eth_gasPrice();
		return \phpEther\Tools\Hex::buffer( $response );;
    }
	
	public function getTransactionByHash($hash) {
		$response = $this->provider->eth_getTransactionByHash($hash);
		return \phpEther\Tools\Hex::buffer( $response ); 
    }
	
	
	public function getLogs(\phpEther\Filter $filter) {
		
        $response = $this->provider->eth_getLogs($filter);
		return \phpEther\Tools\Hex::buffer( $response );
    }
	
	public function getTransactionReceipt($hash) {
        $response=  $this->provider->eth_getTransactionReceipt($hash);
		return \phpEther\Tools\Hex::buffer( $response );
    }
	
	public function getBlockByNumber($no) {
		$no = \phpEther\Tools\Hex::buffer($no);
        $response = $this->provider->eth_getBlockByNumber(Hex::getHex($no));
		return \phpEther\Tools\Hex::buffer( $response );
    }
	
	public function estimateGas(\phpEther\Transaction $tx, $tag = 'latest' ) : Buffer {
        $response =  $this->provider->eth_estimateGas($tx,$tag);
		return \phpEther\Tools\Hex::buffer( $response );;
    }
	
    /**
     * @param array $object
     * @param null $defaultBlock
     * @return string
     */
    public function call(\phpEther\Transaction $object, $defaultBlock = 'latest') : string
    {
        return $this->provider->eth_call($object, $defaultBlock);
		
    }
}