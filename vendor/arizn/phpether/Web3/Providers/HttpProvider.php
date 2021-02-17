<?php
namespace phpEther\Web3\Providers;
use Graze\GuzzleHttp\JsonRpc\Client;

class HttpProvider implements Provider
{
    protected $client;
    protected $id = 0;
	protected $net = NULL;
	protected $chainId = NULL;
    public function __construct(string $url , string $net)
    {
        $this->client = Client::factory($url, ["rpc_error" => true,"debug" => false]);
		$this->determineChainId($net);
    }


    /**
     * @param string $method
     * @param null|array $params
     * @return mixed
     */
   public function request($method, $params = null)
    {
		//var_dump($method,'......................................................#####' , '#########.................',$params );
        $this->id++;
		try {
        	$response = $this->client->send($this->client->request($this->id, $method, $params));
			$x = \Graze\GuzzleHttp\JsonRpc\json_decode($response->getBody());
			//var_dump($method,'......................................................#####',	$x , '#########.................',$params );
		} catch (\GuzzleHttp\Exception\TransferException $e) {
			$err ="";
			if ($e->hasResponse()) {
				$err.=" ". \GuzzleHttp\Psr7\str($e->getResponse());
			}
			$err = " ERROR: ".$e->getMessage();
			throw new \Exception ($err);
		}
        return \Graze\GuzzleHttp\JsonRpc\json_decode($response->getBody());
    }
	
	protected function determineChainId($net){
		$this->net = strtolower($net);
		$chains = ['olympic'=>0,'frontier'=>1,'mainnet'=>1,'homestead'=>1,'metropolis'=>1,'classic'=>1,'expanse'=>1,'morden'=>2,'ropsten'=>3,'rinkeby'=>4,'kovan'=>42];
		$mainnet = ['frontier', 'homestead', 'metropolis','mainnet'];
		if(isset($chains[$this->net]))$this->chainId = $chains[$this->net];
		if(in_array($this->net,$mainnet)) $this->net = 'mainnet';
	}
	
	public function get_chainId(){
		return $this->chainId;
	}
	
	public function get_net(){
		return $this->net;
	}
	

}