<?php
namespace phpEther;

class HD{

	private $bip44 ="m/44'/60'/0'/0";
	/* Wallet  Private Key*/
	private $xpriv;
	/* Wallet  Public Key*/
	private $xpub;
	/* Bitcoin network*/
	private $network;
	/* Wallet Password*/
	private $password;
	/* Wallet  Master Private Key*/
	private $master_xpriv;
	/* Wallet  Master Private Key*/
	private $mnemonic;
	/* Wallet  Master Private Key*/
	private $master_xpub;
	//etherscan.io
	public $apiKey;

	function __construct($xpriv = NULL){
		$this->xpriv = $xpriv;
		$this->network = \BitWasp\Bitcoin\Network\NetworkFactory::bitcoin();
	}

	function getXpub(){
		return $this->xpub;
	}
	function getPassword(){
		return $this->password;
	}
	function getMnemonic(){
		return $this->mnemonic;
	}

	function getXpriv(){
		return $this->xpriv;
	}
	function getMasterXpriv(){
		return $this->master_xpriv;
	}

	public function randomSeed($password=NULL){
		if(!is_null($password)){
			$this->password = $password;
		}
		$ecAdapter =  \BitWasp\Bitcoin\Bitcoin::getEcAdapter();
		$math = new \BitWasp\Bitcoin\Math\Math();
		$random = new \BitWasp\Bitcoin\Crypto\Random\Random();
		$entropy = $random->bytes(64);
		$bip39 = \BitWasp\Bitcoin\Mnemonic\MnemonicFactory::bip39();
		$seedGenerator = new \BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator($bip39);
		// Get the mnemonic
		$mnemonic = $bip39->entropyToMnemonic($entropy);
		$this->mnemonic = $mnemonic;
		// Derive a seed from mnemonic/password
		if(is_null($this->password)){
			$pass = $random->bytes(8);
			$this->password = $pass->getHex();
		}
		$seed = $seedGenerator->getSeed($this->mnemonic, $this->password);
		$master = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromEntropy($seed);
		return $this->masterSeed($master->toExtendedPrivateKey());
	}

	public function recover($mnemonic, $password){
		$this->mnemonic = $mnemonic;
		$this->password = $pasword;
		$bip39 = \BitWasp\Bitcoin\Mnemonic\MnemonicFactory::bip39();
		$seedGenerator = new \BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator($bip39);
		$seed = $seedGenerator->getSeed($this->mnemonic, $this->password);
		$master = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromEntropy($seed);
		return $this->masterSeed($master->toExtendedPrivateKey);
	}

	public function masterSeed($master){
		//Master xpriv
		$master = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($master);
		$master_xpriv = $master->toExtendedPrivateKey($this->network);
		$this->master_xpriv = $master_xpriv;
		$master_xpub = $master->toExtendedPublicKey($this->network); // path is master''
		$this->master_xpub  = $master_xpub;
		$hardened = $master->derivePath($this->bip44);
		$this->xpub = $hardened->toExtendedPublicKey($this->network);
		$this->xpriv = $hardened->toExtendedPrivateKey($this->network);
		return $this;
	}

	public function privateSeed($xpriv){
		//Master xpriv
		$this->xpriv = $xpriv;
		$xpriv = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($xpriv);
		$this->xpub = $xpriv->toExtendedPublicKey($this->network);
		return $this;
	}

	public function publicSeed($xpub){
		//Master xpriv
		$this->xpub = $xpub;
		return $this;
	}

	public function getAddress($index){
		if(empty($this->xpub))throw new \Exception('Public Key is missing');
		$key = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($this->xpub);
		if(strpos($index,'/')!==false)
		$xpub = $key->derivePath($index);
		else
		$xpub = $key->deriveChild($index);
		$publicKey = $xpub->getPublicKey(false);
		$pk = $publicKey->getHex();
		if($publicKey->isCompressed()){
			$pk = \phpEther\Tools\ECDSA::decompress_public_key($pk);
		}
		$pubkey = \BitWasp\Bitcoin\Key\PublicKeyFactory::fromHex($pk);
		return  \phpEther\Account::public_key_to_address($pubkey);
	}

	public function getAccount($index){
		if(empty($this->xpriv))throw new \Exception('Private Key is missing');
		$key = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromExtended($this->xpriv);
		$xpriv = $key->deriveChild($index);
		return new \phpEther\Account($xpriv->getPrivateKey()->getHex());
	}



}
