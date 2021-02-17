<?php
namespace phpEther;

use phpEther\Encoder\Keccak;
use phpEther\Encoder\RplEncoder;
use phpEther\Tools\Hex;
use BitWasp\Buffertools\Buffer;

class Transaction
{
    protected $chainId;
    protected $nonce;
    protected $gasPrice;
    protected $gasLimit;
    protected $to;
	protected $from;
    protected $value;
    protected $data;
	protected $raw;
	protected $hash;
	protected $web3;
    protected $v;
    protected $r;
    protected $s;
    public function __construct(\phpEther\Account $from = NULL, string $to = NULL, int $value = 0, string $data = NULL, int $nonce = NULL, int $gasPrice = NULL, int $gasLimit = NULL , $web3=NULL )
    {
		$this->account = $from;
		$this->to = $to ?Buffer::hex(Hex::cleanPrefix($to)) : new Buffer();
        $this->nonce = is_null($nonce) ? Buffer::int('1'):Buffer::int($nonce) ;
		$this->gasPrice = NULL=== $gasPrice ? Buffer::int('10000000000000') : Buffer::int($gasPrice);
        $this->gasLimit = NULL=== $gasLimit ? Buffer::int('196608') : Buffer::int($gasLimit);	
		$this->chainId =  Buffer::int('3'); // default mainnet;
        $this->data = $data ??  Buffer::hex('00');
		$this->web3 = $web3;
		$this->raw = null;
		$this->hash = new Buffer(); 
		$this->value = Buffer::int($value);
	}
	
	/*Will return the tx hash*/
	public function __toString(){
		return '0x'.$this->hash->getHex();
	}
	
		
	
	/**
     * @param Decimal $nonce
     * @return \phpEther\Transaction 
     */
	public function setNonce($nonce = NULL){
		if(!is_null($nonce)){
			$this->nonce  =$nonce instanceof \BitWasp\Buffertools\Buffer?$nonce:Buffer::Int($nonce);
			return $this;
		}
		if(empty($this->account))
		throw new \Exception('Tx "from" field is required to Determine the Nonce ');
		if(is_null($this->web3))
		throw new \Exception('Please set a Web3 provider');
		$nonce = $this->web3->eth->getTransactionCount($this->account->address);
		$this->nonce  =$nonce instanceof \BitWasp\Buffertools\Buffer?$nonce:Buffer::hex(Hex::cleanPrefix($nonce));
		return $this;
	}
	/**
     * @param Decimal $gasPrice
     * @return \phpEther\Transaction 
     */
	public function setGasPrice($gasPrice = NULL){
		if(!is_null($gasPrice)){
			$this->gasPrice =  $gasPrice;
			return $this;
		}
		if(is_null($this->web3))
		throw new Exception('Please set a Web3 provider');
		$est = $this->web3->eth->gasPrice();
		$this->gasPrice = $est instanceof \BitWasp\Buffertools\Buffer?$est:Buffer::hex(Hex::cleanPrefix($est));
		return $this;
	}
	/**
     * @param Decimal $gasLimit
     * @return \phpEther\Transaction 
     */
	public function setGasLimit($gasLimit= NULL){
		if(!is_null($gasLimit)){
			$this->gasLimit =  $gasLimit;
			return $this;
		}
		if(is_null($this->web3))
		throw new Exception('Please set a Web3 provider');
		$est = $this->web3->eth->estimateGas($this);
		$this->gasLimit = $est instanceof \BitWasp\Buffertools\Buffer?$est:Buffer::hex($est);
		return $this;
	}
	
	public function setTo(\BitWasp\Buffertools\Buffer $to){
		//dd($to->getInt(),$to->getBinary(),$to->getHex());
		$this->to = $to ;
		return $this;
	}
	
	public function setData(\BitWasp\Buffertools\Buffer $data){
		$this->data = $data;
		return $this;
	}


	public function prefill($gl = NULL, $nc = NULL, $gp = NULL ){
		return $this->setNonce($nc)->setGasPrice($gp)->setGasLimit($gl);
	}
	
	public function send(){  
		if(is_null($this->account))
		throw new Exception('Cannot Send Transaction. Specify the From account');
		if(is_null($this->web3))
		throw new Exception('Please set a Web3 provider');
		if(is_null($this->web3))
		if(!is_null($this->account->password)){
			$hash = $this->web3->personal->sendTransaction($this, $this->account->password);
			$this->hash = Buffer::hex(Hex::cleanPrefix($hash));
			return $this;
		}
		
		if(!is_null($this->account->privateKey)){
			$RawTx = $this->sign()->getRaw();
			$hash = $this->web3->eth->sendRawTransaction('0x'.$RawTx->getHex()); 
			$this->hash = Buffer::hex(Hex::cleanPrefix($hash));
			return $this;
		}
		
			throw new Exception('Cannot Send from Account. Both the PrivateKey and Password are Missing');
	
	
	}
	
	
	 /** use erc20 token contract
     * @param string $web3 \phpEther\Web3
     * @return \phpEther\Transaction 
     */
	public function setToken($token){
		$this->token = $token;
		$this->web3 = $token->eth->web3;
		$this->chainId = Buffer::int($token->eth->provider->get_chainId());
		return $this;
	}
	
	 /**
     * @param string $web3 \phpEther\Web3
     * @return \phpEther\Transaction 
     */
	
	public function setWeb3(\phpEther\Web3 $web3){
		$this->web3 = $web3;
		$this->chainId = Buffer::int( $web3->eth->provider->get_chainId());
		return $this;
	}

    /**
     * return \phpEther\Transaction
     */
    
	
	public function getRaw()
    {
       return $this->raw;
    }
	
	public function getHash()
    {
       return $this->hash;
    }
	
	
	function toArray()
	{
		return [ $this->getArray()];
	}
	
	public function getArray(){ // prefixed
		
		$data = [	
				'from'=> is_null($this->account)?"":$this->account->getAddress(),
				'to'=>'0x'.$this->to->getHex(),
				'gas'=>Hex::getHex($this->gasLimit),
				'gasPrice'=>Hex::getHex($this->gasPrice),
				'value'=>Hex::getHex($this->value),
				'data'=>'0x'.$this->data->getHex(),
				'nonce'=>Hex::getHex($this->nonce)
			];
			if($data['value'] =='0x')$data['value']='0x0';
			if($data['to'] =='0x')unset($data['to']);
		return array_filter($data);
	}
	
	
	public function getTx(){ // prefixed
		$data = [	
				'from'=> is_null($this->account)?"":$this->account->getAddress(),
				'to'=>'0x'.$this->to->getHex(),
				'gasLimit'=>$this->gasLimit->getInt(),
				'gasPrice'=> $this->gasPrice->getInt(),
				'value'=> $this->value->getInt(),
				'data'=>'0x'.$this->data->getHex(),
				'nonce'=>$this->nonce->getInt(),
				'hash'=>'0x'.$this->hash->getHex(),
			];
		return json_decode(json_encode($data));
	}
	
    protected  function getInput()
    {
		
        $data = [
            "nonce" => $this->nonce,
            "gasPrice" => $this->gasPrice,
            "gasLimit" => $this->gasLimit,
            "to" => $this->to,
            "value" => $this->value,
            "data" => $this->data,
            "v" => $this->v,
            "r" => $this->r,
            "s" => $this->s,
        ];
		//if(empty($data['to']->getHex()))unset($data['to']);
		return array_filter($data);
    }

    
	private function _nodeSignature(){
		$this->web3->personal->unlockAccount($this->account);
		$signedTX = $this->web3->eth->signTransaction($this);
		$this->raw = Buffer::hex(Hex::cleanPrefix($signedTX->raw));
		return $this;
	}
	
	public function sign(){
		if(is_null($this->account->privateKey)&&!is_null($this->account->password))
		return $this->_nodeSignature();
		return $this->_privateKeySignature();
	}
	
	private function _privateKeySignature(){
		if (is_null($this->account->privateKey)) {
            throw new \Exception("Cannot sign Transaction. NO private Key specified");
        }
		$privateKey = $this->account->privateKey;
        $this->v = new Buffer();
        $this->r = new Buffer();
        $this->s = new Buffer();
        $hash = $this->hash();
		$ecAdapter = \BitWasp\Bitcoin\Bitcoin::getEcAdapter();	
        $sig = $ecAdapter->signCompact(
			$hash,
			$privateKey,
			new \BitWasp\Bitcoin\Crypto\Random\Rfc6979(
				$ecAdapter,
				$privateKey,
				$hash,
				'sha256'
			)
         );	 
		$sign = $sig->getBuffer();
		$recId = $sig->getRecoveryId();
        $this->r = Buffer::hex(Hex::trim(substr($sign->getHex(), -128, 64)));
        $this->s = Buffer::hex(Hex::trim(substr($sign->getHex(), -64)));
        $this->v = Buffer::int($recId + 27 + $this->chainId->getInt() * 2 + 8);
		$this->raw =  $this->serialize();
        return $this;  
    }


   
	 /**
     * @return Buffer
     */
    protected function hash()
    {
        $raw = $this->getInput();
		
        if ($this->chainId->getInt() > 0) {
            $raw['v'] = $this->chainId;
            $raw['r'] = Buffer::hex('00');
            $raw['s'] = Buffer::hex('00');
        } else {
            unset($raw['v']);
            unset($raw['r']);
            unset($raw['s']);
        }
		
        // create hash
        $hash = RplEncoder::encode($raw);
        $shaed = Keccak::hash($hash->getBinary(),'256');
		
        return $shaed;
    }


static function strToHex($string){
    $hex = '';
    for ($i=0; $i<strlen($string); $i++){
        $ord = ord($string[$i]);
        $hexCode = dechex($ord);
        $hex .= substr('0'.$hexCode, -2);
    }
    return strToUpper($hex);
}
static function hexToStr($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}
    /**
     * @return Buffer
     */
    protected function serialize()
    {
        $raw = $this->getInput();
        return RplEncoder::encode($raw);
    }

}

