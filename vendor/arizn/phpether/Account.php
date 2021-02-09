<?php
namespace phpEther;
use BitWasp\Buffertools\Buffer;
use phpEther\Encoder\Keccak;
use phpEther\Encoder\RplEncoder;
use phpEther\Tools\Hex;

class Account
{
	public $privateKey = NULL;
	public $publicKey = NULL;
	public $address = NULL;
	public $password = NULL;
	public function __construct($key =NULL , $net ='mainnet'){
		$this->net = strtolower($net);
		if(is_null($key)){
			$privateKey = \BitWasp\Bitcoin\Key\PrivateKeyFactory::create();
			$publicKey = $privateKey->getPublicKey();
			$this->publicKey = $privateKey->getPublicKey();
			$this->privateKey = $privateKey;
			$this->address =  self::public_key_to_address($this->publicKey);
		}else{
			if(is_array($key)){
				list($this->address, $this->password) = $key;
				$this->address =  $this->address;
			}else{
				$privateKey = \BitWasp\Bitcoin\Key\PrivateKeyFactory::fromHex($key);
				$this->privateKey = $privateKey;
				$this->publicKey = $privateKey->getPublicKey();
				$this->address =  self::public_key_to_address($this->publicKey);
			}
		}
		if(is_null($this->privateKey)&&is_null($this->password))
		throw new Exception('Invalid Account. Both the PrivateKey and Password are Missing');
		
	}
	
	public function getPublicKey(){
		if(is_null($this->publicKey))
		return NULL;
		return $this->publicKey->getHex();
	}
	
	public function getPrivateKey(){
		if(is_null($this->privateKey))
		return NULL;
		return $this->privateKey->getHex();
	}
	
	public function getAddress(){
		return self::ChecksumAddress($this->address);
	}
	
	public static function public_key_to_address($publickey)
    {
        $pubk = mb_substr($publickey->getHex(), -128, 128, 'utf-8'); //remove 04
		//$pubk = \phpEther\Tools\ECDSA::compress_public_key($publickey);
		$r  = Buffer::hex($pubk);
		$address = \phpEther\Encoder\Keccak::hash($r->getBinary(),256);
		//$address = \phpEther\Encoder\Keccak::hash(hex2bin($pubk),256);
		return  '0x'.mb_substr( $address->getHex(), -40, 40, 'utf-8');
    }
	
	public static function ChecksumAddress (string $address) { 
		$lowers = ['0','1','2','3','4','5','6','7'];   
		$address =  str_replace("0x","",strtolower($address));
		$hashed =  \phpEther\Encoder\Keccak::hash($address, 256);
		$hash = str_split($hashed->getHex());
		$address = str_split($address);
		$checksumAddress = "0x";
		for ($i = 0; $i < count($address); $i++ ){ 
			// If ith character is 8 to f then make it uppercase 
			if (!in_array($hash[$i],$lowers) ) {
				$checksumAddress .= strtoupper($address[$i]);
			} else {
				 $checksumAddress .= $address[$i];
			}
		}	
		return $checksumAddress;
	}
	
	
}

