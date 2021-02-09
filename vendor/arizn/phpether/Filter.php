<?php
namespace phpEther;

use phpEther\Tools\Hex;
class Filter
{
	private $fromBlock, $toBlock, $address, $topics;
	
	function __construct($fromBlock, $toBlock, $address, $topics)
	{
		if(is_null($fromBlock)) 
		$this->fromBlock = 'latest';
		elseif(is_numeric($fromBlock)) 
		$this->fromBlock = \BitWasp\Buffertools\Buffer::int($fromBlock);
		else
		$this->fromBlock = $fromBlock;
		if(is_null($toBlock)) 
		$this->toBlock = 'latest';
		elseif(is_numeric($toBlock)) 
		$this->toBlock = \BitWasp\Buffertools\Buffer::int($toBlock);
		else
		$this->toBlock = $toBlock;
		$this->address = $address;
		$this->topics = $topics;
	}
	
	function toArray()
	{
		return [$this->getArray()];
	}
	function getArray()
	{
		return [
			'fromBlock'=>($this->fromBlock instanceof \BitWasp\Buffertools\Buffer)?Hex::getHex($this->fromBlock):$this->fromBlock,
			'toBlock'=>($this->toBlock instanceof \BitWasp\Buffertools\Buffer)?Hex::getHex($this->toBlock):$this->toBlock,
			'address'=>($this->address instanceof \BitWasp\Buffertools\Buffer)?Hex::getHex($this->address):$this->address,
			'topics'=>($this->topics instanceof \BitWasp\Buffertools\Buffer)?Hex::getHex($this->topics):$this->topics
		];
	}
	
	function getIntArray() // return from and To as intergers not hex.
	{
		return [
			'fromBlock'=>($this->fromBlock instanceof \BitWasp\Buffertools\Buffer)?$this->fromBlock->getInt():$this->fromBlock,
			'toBlock'=>($this->toBlock instanceof \BitWasp\Buffertools\Buffer)? $this->toBlock->getInt():$this->toBlock,
			'address'=>($this->address instanceof \BitWasp\Buffertools\Buffer)?Hex::getHex($this->address):$this->address,
			'topics'=>($this->topics instanceof \BitWasp\Buffertools\Buffer)?Hex::getHex($this->topics):$this->topics
		];
	}
}

