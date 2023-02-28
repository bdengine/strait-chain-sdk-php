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
	// 内网测试 20220101
	// 正式 20180818
	private int $chainId = 20180818;
	// 链接地址
	private string $baseUrl = "https://backend.straitchain.com/webclient/api/develop/straits/action";
	// 用户标识
	protected string $appId;
	// 用户密钥
	protected string $appKey;
	// 通行证私钥（钱包私钥）
	protected string $privateKey;
	// 合约执行上限
	protected int $gasLimit = 150000;
	// 默认的费用
	protected string $gasPrice = '0x83156a3e07';
	// 通行证地址（钱包地址）
	protected string $fromAddress;

	protected $abi721;
	protected $abi4907;
	protected $abi1155;
	protected $abiDigital;

	/**
	 * @param string $appId
	 */
	public function setAppId(string $appId)
	{
		$this->appId = $appId;
	}

	/**
	 * @param string $appKey
	 */
	public function setAppKey(string $appKey)
	{
		$this->appKey = $appKey;
	}

	/**
	 * @param string $privateKey
	 */
	public function setPrivateKey(string $privateKey)
	{
		$this->privateKey = $privateKey;
	}

	/**
	 * @param int $gasLimit
	 */
	public function setGasLimit(int $gasLimit)
	{
		$this->gasLimit = $gasLimit;
	}

	/**
	 * @param string $fromAddress
	 */
	public function setFromAddress(string $fromAddress)
	{
		$this->fromAddress = $fromAddress;
	}

	/**
	 * @param string $url
	 */
	public function setUrl( string $url)
	{
		$this->baseUrl = $url;
	}

	function __construct()
	{
		// 默认合约，售卖用的都是这个
		$this->abi721=file_get_contents(__DIR__ . '/ContractDefault721.abi');
		// 租赁合约
		$this->abi4907=file_get_contents(__DIR__ . '/Contract4907.abi');
		// 批量合约
		$this->abi1155=file_get_contents(__DIR__ . '/Contract1155.abi');
		// 数字村正
		$this->abiDigital=file_get_contents(__DIR__ . '/ContractDigital.abi');
 	}

	public function getParams($method, $params, $id): array
	{
		return [
			"jsonrpc" => "2.0",
			"method" => $method,
			"params" => array_values($params),
			// 你们的业务id
			"id" => $id,
		];
	}

	public function request($method, $params, $id)
	{
		$params = $this->getParams($method, $params, $id);
		$jsom = $this->postJson($params);
		return json_decode($jsom);
	}

	public function postJson($param)
	{
		$jsonStr = json_encode($param,JSON_PRETTY_PRINT);
//		var_dump($jsonStr);
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
//		var_dump($result);
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
	public function getContract($abi): Contract
	{
		$url = 'https://mainnet.infura.io/v3/05eb04a6e5cb496fb4a9d937d9a2245e';
		// 为了拿到合约方法的编码
		$web3 = new Web3(new HttpProvider(new HttpRequestManager($url, 5)));
		return new Contract($web3->getProvider(), $abi);
	}

	public function scsSendRawTransaction($txParams)
	{
		var_dump($txParams);
		$transaction = new Transaction($txParams);
		$signedTransaction = $transaction->sign($this->privateKey);
		// 交易如果失败，需要查询为什么，把这个raw和交易哈希发群里问
		return $this->request("scs_sendRawTransaction", ['0x' . $signedTransaction], 1);
	}

	// 获取执行费用单价
	public function scsGasPrice(): string
	{
		return $this->gasPrice;
//		$response = $this->request("scs_gasPrice", array(), 1);
//		return $response->result;
	}

	// 获取transactionCount，即nonce
	public function scsGetTransactionCount($fromAddress, $blockParameter = 'latest')
	{

		# from 即通行证插件账户地址 https://www.straitchain.com/#/down
		// fromAddress
		if (empty($fromAddress)){
			$arr[0] = $this->fromAddress;
		}else{
			$arr[0] = $fromAddress;
		}

		# "latest", "earliest" 或 "pending"
		if (empty($blockParameter)){
			$arr[1] = "latest";
		}else{
			$arr[1] = $blockParameter;
		}

		$response = $this->request("scs_getTransactionCount", $arr, 1);
		return $response->result;
	}

	// 获取余额
	public function scsGetBalance($from)
	{
		// 这里只能是latest，不然金额会不准确
		$param = [
			$from,
			"latest",
		];
		return $this->request("scs_getBalance", $param, 1);
	}

	// 根据块哈希获取指定的块交易内容
	public function scsGetBlockTransactionCountByHash($txHash)
	{
		$param = [
			$txHash,
		];
		return $this->request("scs_getBlockTransactionCountByHash", $param, 1);
	}

	// 根据块号（块高）获取指定的块交易内容
	public function scsGetBlockTransactionCountByNumber($blockNumber)
	{
		$param = [
			$blockNumber,
		];
		return $this->request("scs_getBlockTransactionCountByNumber", $param, 1);
	}

	// 执行并估算一个交易需要的gas用量。
	//该次交易不会写入区块链。
	//注意，由于多种原因，例如EVM的机制 及节点旳性能，估算的数值可能比实际用量大的多。
	public function scsEstimateGas($from, $to, $gasLimit, $value, $data)
	{
		// $gasLimit可以写死，也可以传
		$param = [
			$from,
			$to,
			$gasLimit,
			$this->scsGasPrice(),
			$value,
			$data,
			$this->scsGetTransactionCount($from,'latest'),
		];
		return $this->request("scs_estimateGas", $param, 1);
	}


	public function scsGetBlockByHash($txHash)
	{
		$param = [
			$txHash,
			false,
		];
		return $this->request("scs_getBlockByHash", $param, 1);
	}

	public function scsGetBlockByNumber($txHash)
	{
		$param = [
			$txHash,
			true,
		];
		return $this->request("scs_getBlockByNumber", $param, 1);
	}

	public function scsGetTransactionReceipt($txHash)
	{
		$param = [
			$txHash,
		];
		return $this->request("scs_getTransactionReceipt", $param, 1);
	}

	public function scsGetTransactionByHash($txHash)
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
	 * @param int $contractType 合约类型，0：默认，1：4907租赁合约，2，1155批量合约
	 */
	public function scs_deployContract(int $count, int $contractType = 0)
	{

		$param = [
			$this->fromAddress,
			$count,
			$this->appId,
		];

		if ($contractType != 0) {
			array_push($param, $contractType);
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
	public function scsGetTokenByHash($txHash)
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
		$contract = $this->getContract($this->abi721);
		// 得到合约编码
		$data = $contract->getData('transferFrom', $param['fromAddress'], $param['toAddress'], $param['tokenId']);
		// value 写死，chainId写死
		$trans = [
			'from' => $param['fromAddress'],
			'value' => 0x0,
			"nonce" => $this->scsGetTransactionCount($this->fromAddress),
			"gasPrice" => $this->scsGasPrice(),
			"gas" => $this->gasLimit,
			"to" => $param['contractAddress'],
			"data" => '0x' . $data,
			"chainId" => $this->chainId,
		];
		return $this->scsSendRawTransaction($trans);
	}


	public function transferDrop($toAddress, $drop)
	{
		// 转移是直接交易，就不用调用合约了

		// value 就是转多少了，chainId写死
		$trans = [
			'from' => $this->fromAddress,
			'value' => $drop * pow(10, 18),
			"nonce" => $this->scsGetTransactionCount($this->fromAddress),
			"gasPrice" => $this->scsGasPrice(),
			"gas" => $this->gasLimit,
			"to" => $toAddress,
			"chainId" => $this->chainId,
		];

		return $this->scsSendRawTransaction($trans);
	}

	private function scsCallBase($param, $blockTag = 'latest')
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
	public function scsCall($from, $contractAddress, $encode){
		$param=[
			"from"=>$from,
			"to"=>$contractAddress,
			"gas"=>"0x334455",
			"gasPrice"=>$this->scsGasPrice(),
			"value"=>"",
			"data"=>"0x" . $encode,
		];
		return $this->scsCallBase($param);
	}

	public function ownerOf($param)
	{
		$contract = $this->getContract($this->abi721);
		// 得到合约编码
		$data = $contract->getData('ownerOf', $param['tokenId']);

		return $this->scsCall($this->fromAddress, $param['contractAddress'],$data);
	}

	/**
	 * version 2.2.0
	 * @param $param
	 * @return mixed
	 */
	public function contract4907setUser($param)
	{

		$contract = $this->getContract($this->abi4907);
		// 得到合约编码
		$data = $contract->getData('setUser', $param['tokenId'], $param['toAddress'], $param['expires']);
		// value 写死，chainId写死
		$trans = [
			'from' => $param['fromAddress'],
			'value' => 0x0,
			"nonce" => $this->scsGetTransactionCount($this->fromAddress),
			"gasPrice" => $this->scsGasPrice(),
			"gas" => $this->gasLimit,
			"to" => $param['contractAddress'],
			"data" => '0x' . $data,
			"chainId" => $this->chainId,
		];
		return $this->scsSendRawTransaction($trans);
	}

	/**
	 * version 2.2.0 查询租赁用户地址
	 * @param $param
	 * @return mixed 用户地址，用户不存在就返回null
	 */
	public function contract4907userOf($param)
	{
		$contract = $this->getContract($this->abi4907);
		// 得到合约编码
		$data = $contract->getData('userOf', $param['tokenId']);
		$response = $this->scsCall($this->fromAddress, $param['contractAddress'],$data);
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
		$contract = $this->getContract($this->abi4907);
		// 得到合约编码
		$data = $contract->getData('userExpires', $param['tokenId']);
		$response = $this->scsCall($this->fromAddress, $param['contractAddress'],$data);

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
	 * @param $txHash string 铸造返回哈希
	 * @return mixed 铸造结果，交易哈希相同
	 */
	public function scsDigitalCollectionList(string $txHash){
		return $this->request("scs_digital_collection_list", [$txHash], 1);
	}

	public function scsGetEvidenceContractAddress()
	{
		$result = $this->request("scs_get_evidence_contract_address",[],1);
		return $result->result;
	}

	public function dcEvidenceSignHex($param){
		$contractAddress = $this->scsGetEvidenceContractAddress();

		$contract = $this->getContract($this->abiDigital);
		// 得到合约编码
		$data = $contract->getData('evidenceContentEvent', $param['cid'],$param['content']);
		// value 写死，chainId写死
		$txParams = [
			'from' => $param['fromAddress'],
			'value' => 0x0,
			"nonce" => $this->scsGetTransactionCount($this->fromAddress),
			"gasPrice" => $this->scsGasPrice(),
			"gas" => $this->gasLimit,
			"to" => $contractAddress,
			"data" => '0x' . $data,
			"chainId" => $this->chainId,
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

	public function scs1155NftMint($mintParam)
	{
		array_unshift($mintParam, $this->appId);
		$str = $this->signByAppKey($mintParam);
		$mintParam['sign'] = $str;
		return $this->request("scs_1155_nft_mint", $mintParam, 1);
	}

	public function contract1155TransferNft($param)
	{
		$contract = $this->getContract($this->abi1155);
		// 得到合约编码
		$data = $contract->getData('safeTransferFrom', $param['fromAddress'], $param['toAddress'], $param['tokenId'], $param['amount'], $param['data']);
		// value 写死，chainId写死
		$trans = [
			'from' => $param['fromAddress'],
			'value' => 0x0,
			"nonce" => $this->scsGetTransactionCount($this->fromAddress),
			"gasPrice" => $this->scsGasPrice(),
			"gas" => $this->gasLimit,
			"to" => $param['contractAddress'],
			"data" => '0x' . $data,
			"chainId" => $this->chainId,
		];
		return $this->scsSendRawTransaction($trans);
	}

	public function contract1155BalanceOf(array $param)
	{
		$contract = $this->getContract($this->abi1155);
		// 得到合约编码
		$data = $contract->getData('balanceOf', $param['account'], $param['tokenId']);
		$response = $this->scsCall($param['fromAddress'],$param['contractAddress'],$data);
		$result = str_replace('0x', '', $response->result);
		return hexdec($result);
	}

	public function contract1155BalanceOfBatch(array $param)
	{
		$contract = $this->getContract($this->abi1155);
		// 得到合约编码
		$data = $contract->getData('balanceOfBatch', $param['accounts'], $param['tokenIds']);
		$response = $this->scsCall($param['fromAddress'],$param['contractAddress'],$data);
		return $response->result;
	}

	public function contract1155VerificationNft(array $param)
	{
		$contract = $this->getContract($this->abi1155);
		// 得到合约编码
		$data = $contract->getData('verificationNft', $param['tokenId']);
		$response = $this->scsCall($param['fromAddress'],$param['contractAddress'],$data);
		return $contract->getEthabi()->decodeParameters(['uint256','address','uint256','string[]'],$response->result);
	}

}

