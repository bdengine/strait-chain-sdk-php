<?php


namespace strait_chain;
require_once __DIR__ . '/../../vendor/autoload.php';

use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Web3;
use Web3p\EthereumTx\Transaction;

class StraitChainClient
{
	private $baseUrl = "https://backend.straitchain.com/webclient/api/develop/straits/action";
	protected $appId;
	protected $appKey;
	protected $privateKey;
	protected $gasLimit = 150000;
	protected $fromAddress;


	function __construct()
	{

	}

	public function get_params($method, $params, $id)
	{
		return [
			"jsonrpc" => "2.0",
			"method" => $method,
			"params" => array_values($params),
			// 你们的业务id
			"id" => $id,
		];
	}

	public function get_result($json)
	{
		return json_decode($json);
	}

	public function request($method, $params, $id)
	{
		$params = $this->get_params($method, $params, $id);
		$jsom = $this->http_post_json($params);
		return $this->get_result($jsom);
	}

	public function http_post_json($param)
	{
		$jsonStr = json_encode($param,JSON_PRETTY_PRINT);
		var_dump($jsonStr);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_URL, $this->baseUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
		$result = curl_exec($ch);
		var_dump($result);
		return $result;
	}

	/**
	 * 地址去0
	 * version 2.2.0
	 * @param mixed $address 地址，如果没有地址则返回null
	 * @return string|null 去0后的地址
	 */
	public function removeExtraZero($address): ?string
	{
        if (strpos((string)$address,'0x') === 0) {
	        $address = str_replace("0x", "",$address);
        }
		if (empty($address) == true){
			return null;
		}
		// 去0
		$address = str_replace("0000", "",$address);
		// 长度固定40位
        $fillZeroNumber = 40 -strlen($address);
        if ($fillZeroNumber>0){
	        for ($i = 0; $i < $fillZeroNumber; $i++) {
		        $address = '0' . $address;
	        }
        }
        return "0x" . $address;
    }

	/**
	 * 返回合约
	 * @return Contract 合约实体类
	 */
	public function getContract($abi){
		$url = 'https://kovan.infura.io/v3/8f762549c8c341388ac03552835a0358';
		// 为了拿到合约方法的编码
		$web3 = new Web3(new HttpProvider(new HttpRequestManager($url, 5)));
		return new Contract($web3->getProvider(), $abi);
	}

	public function scs_sendRawTransaction($txParams)
	{
		var_dump($txParams);
		$transaction = new Transaction($txParams);
		$signedTransaction = $transaction->sign($this->privateKey);
		// 交易如果失败，需要查询为什么，把这个raw和交易哈希发群里问
		return $this->request("scs_sendRawTransaction", ['0x' . $signedTransaction], 1);
	}

	// 获取执行费用单价
	public function scs_gasPrice()
	{
		$response = $this->request("scs_gasPrice", array(), 1);
		return $response->result;
	}

	// 获取transactionCount，即nonce
	public function scs_getTransactionCount()
	{

		# from 即通行证插件账户地址 https://www.straitchain.com/#/down
		// fromAddress
//		$arr[0] = "0xc4244f49522c32e6181b759f35be5efa2f19d7f9";
		$arr[0] = $this->fromAddress;
		# "latest", "earliest" 或 "pending"
		$arr[1] = "latest";
//		$arr[1] = $blockParameter;
		#
		$response = $this->request("scs_getTransactionCount", $arr, 1);
		return $response->result;

	}

	// 获取余额
	public function scs_getBalance($from)
	{
		$param = [
			$from,
			"latest",
		];
		return $this->request("scs_getBalance", $param, 1);
	}

	// 根据块哈希获取指定的块交易内容
	public function scs_getBlockTransactionCountByHash($txHash)
	{
		$param = [
			$txHash,
		];
		return $this->request("scs_getBlockTransactionCountByHash", $param, 1);
	}

	// 根据块号（块高）获取指定的块交易内容
	public function scs_getBlockTransactionCountByNumber($blockNumber)
	{
		$param = [
			$blockNumber,
		];
		return $this->request("scs_getBlockTransactionCountByNumber", $param, 1);
	}

	// 执行并估算一个交易需要的gas用量。该次交易不会写入区块链。注意，由于多种原因，例如EVM的机制 及节点旳性能，估算的数值可能比实际用量大的多。
	public function scs_estimateGas($from, $to, $gasLimit, $value, $data)
	{
		// $gasLimit可以写死，也可以传
		$param = [
			$from,
			$to,
			$gasLimit,
			$this->scs_gasPrice(),
			$value,
			$data,
			$this->scs_getTransactionCount(),
		];
		return $this->request("scs_estimateGas", $param, 1);
	}


	public function scs_getBlockByHash($txHash)
	{
		// $gasLimit可以写死，也可以传
		$param = [
			$txHash,
			false,
		];
		return $this->request("scs_getBlockByHash", $param, 1);
	}

	public function scs_getBlockByNumber($txHash)
	{
		$param = [
			$txHash,
			true,
		];
		return $this->request("scs_getBlockByNumber", $param, 1);
	}

	public function scs_getTransactionReceipt($txHash)
	{
		$param = [
			$txHash,
		];
		return $this->request("scs_getTransactionReceipt", $param, 1);
	}

	public function scs_getTransactionByHash($txHash)
	{
		$param = [
			$txHash,
		];
		return $this->request("scs_getTransactionByHash", $param, 1);
	}

	public function scs_blockNumber()
	{
		$param = [

		];
		return $this->request("scs_blockNumber", $param, 1);
	}

	/**
	 *
	 * $params = [
	 * 'nftName'         => '测试',
	 * 'cid'             => '',
	 * 'nftURI'          => 'http://xxx/xx/xxxx.json',
	 * 'copyright'       => '科技',
	 * 'issuer'          => '科技',
	 * 'operator'        => '科技',
	 * 'remark'          => '',
	 * 'count'           => '1',
	 * 'owner'           => '0x121231',
	 * 'contractAddress' => '0x121231',
	 * 'collectSn'       => '-1',
	 * 'serviceId'       => '',
	 * ];
	 *
	 *
	 */

	public function scs_nft_mint($mintParam)
	{
		array_unshift($mintParam, $this->appId);
		$str = $this->signByAppKey($mintParam);
		$mintParam['sign'] = $str;
		return $this->request("scs_nft_mint", $mintParam, 1);
	}

	/**
	 * 参数同scs_nft_mint一样，但是count要为1
	 * 返回的是藏品信息
	 *
	 */
	public function scs_nft_mint_alone($mintParam)
	{
		array_unshift($mintParam, $this->appId);
		$str = $this->signByAppKey($mintParam);
		$mintParam['sign'] = $str;
		return $this->request("scs_nft_mint_alone", $mintParam, 1);
	}

	public function signByAppKey($param)
	{
		$param['appKey'] = $this->appKey;
		// 字符串按照  &  拼接
		$str = implode("&", $param);
		return md5($str);
	}

	/**
	 * 部署合约
	 * @param int $count 部署个数
	 * @param int $contractype 合约类型，0：默认，1：合约4907
	 */
	public function scs_deployContract(int $count, int $contractype)
	{

		$param = [
			$this->fromAddress,
			$count,
			$this->appId,
		];

		if ($contractype != 0) {
			array_push($param, $contractype);
		}

		return $this->request("scs_deploy_contract", $param, 1);
	}

	/**
	 * scs_deployContract 返回的交易哈希作为参数
	 * 返回合约地址
	 * @param string $txHash  返回的交易哈希
	 */
	public function scs_contractAddressByHash($txHash)
	{
		$param = [
			$txHash,
		];
		return $this->request("scs_contractAddressByHash", $param, 1);
	}

	/**
	 * 根据铸造哈希获取铸造结果
	 */
	public function scs_getTokenByHash($txHash)
	{
		$param = [
			$txHash,
		];
		return $this->request("scs_getTokenByHash", $param, 1);
	}


	/**
	 * 转移nft
	 */
	public function transferNft($param)
	{
		$contract = $this->getContract($this->testAbi);
		// 得到合约编码
		$data = $contract->getData('transferFrom', $param['fromAddress'], $param['toAddress'], $param['tokenId']);
		// value 写死，chainId写死
		$trans = [
			'from' => $param['fromAddress'],
			'value' => 0x0,
			"nonce" => $this->scs_getTransactionCount(),
			"gasPrice" => $this->scs_gasPrice(),
			"gas" => $this->gasLimit,
			"to" => $param['contractAddress'],
			"data" => '0x' . $data,
			// 正式
			"chainId" => 20180818,
		];
		return $this->scs_sendRawTransaction($trans);
	}


	public function transferDrop($toAddress, $drop)
	{
		// 转移是直接交易，就不用调用合约了

		// value 就是转多少了，chainId写死
		$trans = [
			'from' => $this->fromAddress,
			'value' => $drop * pow(10, 18),
			"nonce" => $this->scs_getTransactionCount(),
			"gasPrice" => $this->scs_gasPrice(),
			"gas" => $this->gasLimit,
			"to" => $toAddress,
			"chainId" => 20180818,
		];

		return $this->scs_sendRawTransaction($trans);
	}

	private function scs_call_base($param, $blockTag)
	{
		$trans = [
			$param,
			$blockTag,
		];
		return $this->request("scs_call", $trans, 1);
	}

	/**
	 * https://docs.qq.com/doc/DUEt6R3l5Rk9Ka2x3?_t=1648433458277 3.5.19 scs_call
	 * @param $from
	 * @param $contractAddress
	 * @param $encode
	 * @return mixed
	 */
	public function scs_call($from,$contractAddress,$encode){
		$gasPrice = $this->scs_gasPrice();
		$param=[
			"from"=>$from,
			"to"=>$contractAddress,
			"gas"=>"0x334455",
			"gasPrice"=>$gasPrice,
			"value"=>"0x0",
			"data"=>"0x" . $encode,
		];
		return $this->scs_call_base($param,"latest");
	}

	public function ownerOf($param)
	{
		$contract = $this->getContract($this->testAbi);
		// 得到合约编码
		$data = $contract->getData('ownerOf', $param['tokenId']);

		return $this->scs_call($this->fromAddress, $param['contractAddress'],$data);
	}

	/**
	 * version 2.2.0
	 * @param $param
	 * @return mixed
	 */
	public function contract4907setUser($param)
	{

		$contract = $this->getContract($this->testAbi);
		// 得到合约编码
		$data = $contract->getData('setUser', $param['tokenId'], $param['toAddress'], $param['expires']);
		// value 写死，chainId写死
		$trans = [
			'from' => $param['fromAddress'],
			'value' => 0x0,
			"nonce" => $this->scs_getTransactionCount(),
			"gasPrice" => $this->scs_gasPrice(),
			"gas" => $this->gasLimit,
			"to" => $param['contractAddress'],
			"data" => '0x' . $data,
			// 内网测试
			"chainId" => 20220101,
			// 生产正式
//			"chainId" => 20180818,
		];
		return $this->scs_sendRawTransaction($trans);
	}

	/**
	 * version 2.2.0 查询租赁用户地址
	 * @param $param
	 * @return mixed 用户地址，用户不存在就返回null
	 */
	public function contract4907userOf($param)
	{
		$contract = $this->getContract($this->testAbi);
		// 得到合约编码
		$data = $contract->getData('userOf', $param['tokenId']);
		$response = $this->scs_call($this->fromAddress, $param['contractAddress'],$data);
		$address = $this->removeExtraZero($response->result);
		var_dump($address);
		return $this->removeExtraZero($response->result);
	}

	/**
	 * 用户租赁的到期时间
	 * version 2.2.0
	 * @param $param
	 * @return mixed
	 */
	public function contract4907userExpires($param)
	{
		$contract = $this->getContract($this->testAbi);
		// 得到合约编码
		$data = $contract->getData('userExpires', $param['tokenId']);
		$response = $this->scs_call($this->fromAddress, $param['contractAddress'],$data);

		return hexdec($response->result);
	}

	/**
	 * 数字存证
	 * version 2.1.0
	 * @param $mintParam
	 * @return mixed 交易哈希
	 */
	public function scsDigitalCollectionMint($mintParam){
		array_unshift($mintParam, $this->appId);
		$str = $this->signByAppKey($mintParam);
		$mintParam['sign'] = $str;
		return $this->request("scs_digital_collection_mint", $mintParam, 1);
	}

	/**
	 * 获取铸造结果
	 * @param $txHash 铸造返回哈希
	 * @return mixed 铸造结果，交易哈希相同
	 */
	public function scsDigitalCollectionList($txHash){
		return $this->request("scs_digital_collection_list", [$txHash], 1);
	}

	public function scsGetEvidenceContractAddress()
	{
		$result = $this->request("scs_get_evidence_contract_address",[],1);
		return $result->result;
	}

	public function dcEvidenceSignHex($param){
		$contractAddress = $this->scsGetEvidenceContractAddress();

		$contract = $this->getContract($this->digitalAbi);
		// 得到合约编码
		$data = $contract->getData('evidenceContentEvent', $param['cid'],$param['content']);
		// value 写死，chainId写死
		$txParams = [
			'from' => $param['fromAddress'],
			'value' => 0x0,
			"nonce" => $this->scs_getTransactionCount(),
			"gasPrice" => $this->scs_gasPrice(),
			"gas" => $this->gasLimit,
			"to" => $contractAddress,
			"data" => '0x' . $data,
			// 正式
//			"chainId" => 20180818,
			// 内网测试
			"chainId" => 20220101,
		];

		$transaction = new Transaction($txParams);
		return $transaction->sign($this->privateKey);
	}

	public function scsExistingEvidence($param){
		array_unshift($param, $this->appId);
		$str = $this->signByAppKey($param);
		$param['sign'] = $str;
		return $this->request("scs_existing_evidence",$param,1);
	}

	public function scsDigitalCollectionTransaction($param){
		array_unshift($param, $this->appId);
		$str = $this->signByAppKey($param);
		$param['sign'] = $str;
		return $this->request("scs_digital_collection_transaction",$param,1);
	}

	protected $testAbi =
		'[
  {
    "inputs": [
      {
        "internalType": "address payable",
        "name": "addr",
        "type": "address"
      },
      {
        "internalType": "uint256",
        "name": "initCount",
        "type": "uint256"
      }
    ],
    "stateMutability": "nonpayable",
    "type": "constructor"
  },
  {
    "anonymous": false,
    "inputs": [
      {
        "indexed": true,
        "internalType": "address",
        "name": "owner",
        "type": "address"
      },
      {
        "indexed": true,
        "internalType": "address",
        "name": "approved",
        "type": "address"
      },
      {
        "indexed": true,
        "internalType": "uint256",
        "name": "tokenId",
        "type": "uint256"
      }
    ],
    "name": "Approval",
    "type": "event"
  },
  {
    "anonymous": false,
    "inputs": [
      {
        "indexed": true,
        "internalType": "address",
        "name": "owner",
        "type": "address"
      },
      {
        "indexed": true,
        "internalType": "address",
        "name": "operator",
        "type": "address"
      },
      {
        "indexed": false,
        "internalType": "bool",
        "name": "approved",
        "type": "bool"
      }
    ],
    "name": "ApprovalForAll",
    "type": "event"
  },
  {
    "anonymous": false,
    "inputs": [
      {
        "indexed": true,
        "internalType": "uint256",
        "name": "tokenId",
        "type": "uint256"
      },
      {
        "indexed": true,
        "internalType": "address",
        "name": "owner",
        "type": "address"
      },
      {
        "indexed": false,
        "internalType": "string",
        "name": "name",
        "type": "string"
      },
      {
        "indexed": false,
        "internalType": "string",
        "name": "cid",
        "type": "string"
      },
      {
        "indexed": false,
        "internalType": "string",
        "name": "nftURI",
        "type": "string"
      },
      {
        "indexed": false,
        "internalType": "string",
        "name": "copyrighter",
        "type": "string"
      },
      {
        "indexed": false,
        "internalType": "string",
        "name": "issuer",
        "type": "string"
      },
      {
        "indexed": false,
        "internalType": "string",
        "name": "operator",
        "type": "string"
      },
      {
        "indexed": false,
        "internalType": "string",
        "name": "remark",
        "type": "string"
      }
    ],
    "name": "NFTItemCreated",
    "type": "event"
  },
  {
    "anonymous": false,
    "inputs": [
      {
        "indexed": true,
        "internalType": "address",
        "name": "from",
        "type": "address"
      },
      {
        "indexed": true,
        "internalType": "address",
        "name": "to",
        "type": "address"
      },
      {
        "indexed": true,
        "internalType": "uint256",
        "name": "tokenId",
        "type": "uint256"
      }
    ],
    "name": "Transfer",
    "type": "event"
  },
  {
    "anonymous": false,
    "inputs": [
      {
        "indexed": true,
        "internalType": "uint256",
        "name": "tokenId",
        "type": "uint256"
      },
      {
        "indexed": true,
        "internalType": "address",
        "name": "user",
        "type": "address"
      },
      {
        "indexed": false,
        "internalType": "uint64",
        "name": "expires",
        "type": "uint64"
      }
    ],
    "name": "UpdateUser",
    "type": "event"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "_tokenId",
        "type": "uint256"
      }
    ],
    "name": "_burn",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_approved",
        "type": "address"
      },
      {
        "internalType": "uint256",
        "name": "_tokenId",
        "type": "uint256"
      }
    ],
    "name": "approve",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_owner",
        "type": "address"
      }
    ],
    "name": "balanceOf",
    "outputs": [
      {
        "internalType": "uint256",
        "name": "",
        "type": "uint256"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "_tokenId",
        "type": "uint256"
      }
    ],
    "name": "getApproved",
    "outputs": [
      {
        "internalType": "address",
        "name": "",
        "type": "address"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "getNftLockCount",
    "outputs": [
      {
        "internalType": "uint256",
        "name": "lockCount",
        "type": "uint256"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "_tokenId",
        "type": "uint256"
      }
    ],
    "name": "getNftLockCount",
    "outputs": [
      {
        "internalType": "uint256",
        "name": "nftLock",
        "type": "uint256"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "getNftLockCountFlag",
    "outputs": [
      {
        "internalType": "bool",
        "name": "lockCountFlag",
        "type": "bool"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "_tokenId",
        "type": "uint256"
      }
    ],
    "name": "getNftLockData",
    "outputs": [
      {
        "internalType": "uint256[]",
        "name": "nftLock",
        "type": "uint256[]"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "getNftLockTime",
    "outputs": [
      {
        "internalType": "uint256",
        "name": "locktimeInit",
        "type": "uint256"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "getNftLockTimeFlag",
    "outputs": [
      {
        "internalType": "bool",
        "name": "lockTimeFlag",
        "type": "bool"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "getNftMaxCount",
    "outputs": [
      {
        "internalType": "uint256",
        "name": "nftMaxCount",
        "type": "uint256"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "getOwnerOfContract",
    "outputs": [
      {
        "internalType": "address",
        "name": "addr",
        "type": "address"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "getOwnerTokenId",
    "outputs": [
      {
        "internalType": "uint256[]",
        "name": "",
        "type": "uint256[]"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_owner",
        "type": "address"
      },
      {
        "internalType": "address",
        "name": "_operator",
        "type": "address"
      }
    ],
    "name": "isApprovedForAll",
    "outputs": [
      {
        "internalType": "bool",
        "name": "",
        "type": "bool"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "name",
    "outputs": [
      {
        "internalType": "string",
        "name": "",
        "type": "string"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "string",
        "name": "nftName",
        "type": "string"
      },
      {
        "internalType": "string",
        "name": "cid",
        "type": "string"
      },
      {
        "internalType": "string",
        "name": "nftURI",
        "type": "string"
      },
      {
        "internalType": "string",
        "name": "copyrighter",
        "type": "string"
      },
      {
        "internalType": "string",
        "name": "issuer",
        "type": "string"
      },
      {
        "internalType": "string",
        "name": "operator",
        "type": "string"
      },
      {
        "internalType": "string",
        "name": "remark",
        "type": "string"
      }
    ],
    "name": "nft_mint",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "_tokenId",
        "type": "uint256"
      }
    ],
    "name": "ownerOf",
    "outputs": [
      {
        "internalType": "address",
        "name": "",
        "type": "address"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_from",
        "type": "address"
      },
      {
        "internalType": "address",
        "name": "_to",
        "type": "address"
      },
      {
        "internalType": "uint256",
        "name": "_tokenId",
        "type": "uint256"
      }
    ],
    "name": "safeTransferFrom",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_from",
        "type": "address"
      },
      {
        "internalType": "address",
        "name": "_to",
        "type": "address"
      },
      {
        "internalType": "uint256",
        "name": "_tokenId",
        "type": "uint256"
      },
      {
        "internalType": "bytes",
        "name": "_data",
        "type": "bytes"
      }
    ],
    "name": "safeTransferFrom",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_operator",
        "type": "address"
      },
      {
        "internalType": "bool",
        "name": "_approved",
        "type": "bool"
      }
    ],
    "name": "setApprovalForAll",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "count",
        "type": "uint256"
      }
    ],
    "name": "setNftLockCount",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "bool",
        "name": "flag",
        "type": "bool"
      }
    ],
    "name": "setNftLockCountFlag",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "time",
        "type": "uint256"
      }
    ],
    "name": "setNftLockTime",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "bool",
        "name": "flag",
        "type": "bool"
      }
    ],
    "name": "setNftLockTimeFlag",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "tokenId",
        "type": "uint256"
      },
      {
        "internalType": "address",
        "name": "user",
        "type": "address"
      },
      {
        "internalType": "uint64",
        "name": "expires",
        "type": "uint64"
      }
    ],
    "name": "setUser",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "bytes4",
        "name": "interfaceId",
        "type": "bytes4"
      }
    ],
    "name": "supportsInterface",
    "outputs": [
      {
        "internalType": "bool",
        "name": "",
        "type": "bool"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "symbol",
    "outputs": [
      {
        "internalType": "string",
        "name": "",
        "type": "string"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "index",
        "type": "uint256"
      }
    ],
    "name": "tokenByIndex",
    "outputs": [
      {
        "internalType": "uint256",
        "name": "",
        "type": "uint256"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "owner",
        "type": "address"
      },
      {
        "internalType": "uint256",
        "name": "index",
        "type": "uint256"
      }
    ],
    "name": "tokenOfOwnerByIndex",
    "outputs": [
      {
        "internalType": "uint256",
        "name": "",
        "type": "uint256"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "tokenId",
        "type": "uint256"
      }
    ],
    "name": "tokenURI",
    "outputs": [
      {
        "internalType": "string",
        "name": "",
        "type": "string"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [],
    "name": "totalSupply",
    "outputs": [
      {
        "internalType": "uint256",
        "name": "",
        "type": "uint256"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_from",
        "type": "address"
      },
      {
        "internalType": "address",
        "name": "_to",
        "type": "address"
      },
      {
        "internalType": "uint256",
        "name": "_tokenId",
        "type": "uint256"
      }
    ],
    "name": "transferFrom",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "newOwner",
        "type": "address"
      }
    ],
    "name": "transferOfOwnership",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "tokenId",
        "type": "uint256"
      }
    ],
    "name": "userExpires",
    "outputs": [
      {
        "internalType": "uint256",
        "name": "",
        "type": "uint256"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "tokenId",
        "type": "uint256"
      }
    ],
    "name": "userOf",
    "outputs": [
      {
        "internalType": "address",
        "name": "",
        "type": "address"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "_tokenId",
        "type": "uint256"
      }
    ],
    "name": "verificationNft",
    "outputs": [
      {
        "components": [
          {
            "internalType": "uint256",
            "name": "tokenId",
            "type": "uint256"
          },
          {
            "internalType": "address",
            "name": "owner",
            "type": "address"
          },
          {
            "internalType": "string",
            "name": "name",
            "type": "string"
          },
          {
            "internalType": "string",
            "name": "cid",
            "type": "string"
          },
          {
            "internalType": "string",
            "name": "nftURI",
            "type": "string"
          },
          {
            "internalType": "string",
            "name": "copyrighter",
            "type": "string"
          },
          {
            "internalType": "string",
            "name": "Issuer",
            "type": "string"
          },
          {
            "internalType": "string",
            "name": "operator",
            "type": "string"
          },
          {
            "internalType": "string",
            "name": "remark",
            "type": "string"
          }
        ],
        "internalType": "struct SCS721NFTMint.NFTItem",
        "name": "",
        "type": "tuple"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  }
]';

	protected $digitalAbi='[
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": false,
				"internalType": "string",
				"name": "cid",
				"type": "string"
			},
			{
				"indexed": false,
				"internalType": "string",
				"name": "content",
				"type": "string"
			}
		],
		"name": "evidenceContentEvent",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "string",
				"name": "cid",
				"type": "string"
			},
			{
				"internalType": "string",
				"name": "content",
				"type": "string"
			}
		],
		"name": "evidence",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "string",
				"name": "cid",
				"type": "string"
			}
		],
		"name": "getContentByCid",
		"outputs": [
			{
				"internalType": "string",
				"name": "content",
				"type": "string"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "totalSupply",
		"outputs": [
			{
				"internalType": "uint256",
				"name": "",
				"type": "uint256"
			}
		],
		"stateMutability": "view",
		"type": "function"
	}
]';

	/**
	 * @param mixed $appId
	 */
	public function setAppId($appId)
	{
		$this->appId = $appId;
	}

	/**
	 * @param mixed $appKey
	 */
	public function setAppKey($appKey)
	{
		$this->appKey = $appKey;
	}

	/**
	 * @param mixed $privateKey
	 */
	public function setPrivateKey($privateKey)
	{
		$this->privateKey = $privateKey;
	}

	/**
	 * @param int $gasLimit
	 */
	public function setGasLimit($gasLimit)
	{
		$this->gasLimit = $gasLimit;
	}

	/**
	 * @param mixed $fromAddress
	 */
	public function setFromAddress($fromAddress)
	{
		$this->fromAddress = $fromAddress;
	}

	/**
	 * @param mixed $url
	 */
	public function setUrl($url)
	{
		$this->baseUrl = $url;
	}


}