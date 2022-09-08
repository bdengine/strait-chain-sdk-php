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
    protected $gasLimit = 110000;
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
        $json = json_encode($params);
        $jsom = $this->http_post_json($json);
        return $this->get_result($jsom);
    }

    public function http_post_json($jsonStr)
    {


        echo $jsonStr;
        $ch = curl_init();
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
        $arr[0] = "0xc4244f49522c32e6181b759f35be5efa2f19d7f9";
        # "latest", "earliest" 或 "pending"
        $arr[1] = "latest";
        #
        $response = $this->request("scs_getTransactionCount", $arr, 1);
        return $response->result;

    }

    public function scs_sendRawTransaction($param)
    {
        $web3 = new Web3(new HttpProvider(new HttpRequestManager("'https://kovan.infura.io/v3/a0d810fdff64493baba47278f3ebad27'", 5)));

        $contract = new Contract($web3->provider, $this->testAbi);
        $data = '0x' . $contract->getData('transferFrom', $param["fromAddress"], $param["toAddress"], $param["tokenId"]);


        $txParams = [
            "nonce" => $this->scs_getTransactionCount(),
            "gasPrice" => $this->scs_gasPrice(),
            "gasLimit" => $this->gasLimit,
            "from" => $param["fromAddress"],
            "to" => $param["contractAddress"],
            "data" => $data,
        ];
        $transaction = new Transaction($txParams);
        $signedTransaction = $transaction->sign($this->privateKey);


        return $this->request("scs_sendRawTransaction", [$signedTransaction], 1);
    }

    // 获取单价
    public function scs_getBalance($from)
    {
        $param = [
            $from,
            "latest",
        ];
        return $this->request("scs_getBalance",$param,1);
    }

    // 根据块哈希获取指定的块交易内容
    public function scs_getBlockTransactionCountByHash($txHash)
    {
        $param = [
            $txHash,
        ];
        return $this->request("scs_getBlockTransactionCountByHash",$param,1);
    }

    // 根据块号（块高）获取指定的块交易内容
    public function scs_getBlockTransactionCountByNumber($blockNumber)
    {
        $param = [
            $blockNumber,
        ];
        return $this->request("scs_getBlockTransactionCountByNumber",$param,1);
    }

    // 执行并估算一个交易需要的gas用量。该次交易不会写入区块链。注意，由于多种原因，例如EVM的机制 及节点旳性能，估算的数值可能比实际用量大的多。
    public function scs_estimateGas($from,$to,$gasLimit,$value,$data)
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
        return $this->request("scs_estimateGas",$param,1);
    }


    public function scs_getBlockByHash($txHash)
    {
        // $gasLimit可以写死，也可以传
        $param = [
            $txHash,
            false,
        ];
        return $this->request("scs_getBlockByHash",$param,1);
    }

    public function scs_getBlockByNumber($txHash)
    {
        $param = [
            $txHash,
            true,
        ];
        return $this->request("scs_getBlockByNumber",$param,1);
    }

    public function scs_getTransactionReceipt($txHash)
    {
        $param = [
            $txHash,
        ];
        return $this->request("scs_getTransactionReceipt",$param,1);
    }

    public function scs_getTransactionByHash($txHash)
    {
        $param = [
            $txHash,
        ];
        return $this->request("scs_getTransactionByHash",$param,1);
    }

    public function scs_blockNumber()
    {
        $param = [

        ];
        return $this->request("scs_blockNumber",$param,1);
    }

    /**
     *
    $params = [
    'nftName'         => '测试',
    'cid'             => '',
    'nftURI'          => 'http://xxx/xx/xxxx.json',
    'copyright'       => '科技',
    'issuer'          => '科技',
    'operator'        => '科技',
    'remark'          => '',
    'count'           => '1',
    'owner'           => '0x121231',
    'contractAddress' => '0x121231',
    'collectSn'       => '-1',
    'serviceId'       => '',
    ];
     *
     *
     */

    public function scs_nft_mint($mintParam)
    {
        array_unshift($mintParam,$this->appId);
        $str = $this->signByAppKey($mintParam);
        $mintParam['sign'] = $str;
        return $this->request("scs_nft_mint",$mintParam,1);
    }

    /**
     * 参数同scs_nft_mint一样，但是count要为1
     * 返回的是藏品信息
     *
     */
    public function scs_nft_mint_alone($mintParam)
    {
        array_unshift($mintParam,$this->appId);
        $str = $this->signByAppKey($mintParam);
        $mintParam['sign'] = $str;
        return $this->request("scs_nft_mint_alone",$mintParam,1);
    }

    public function signByAppKey($param)
    {
        $param['appKey'] = $this->appKey;
        // 字符串按照  &  拼接
        $str = implode("&", $param);
        return md5($str);
    }

    /**
     * @param $count 部署个数
     */
    public function scs_deployContract($count)
    {
        $param=[
            $this->fromAddress,
            $count,
            $this->appId,
        ];
        return $this->request("scs_deploy_contract",$param,1);
    }

    /**
     * scs_deployContract 返回的交易哈希作为参数
     * 返回合约地址
     * @param $txHash  返回的交易哈希
     */
    public function scs_contractAddressByHash($txHash)
    {
        $param=[
            $txHash,
        ];
        return $this->request("scs_contractAddressByHash",$param,1);
    }

    /**
     * 根据铸造哈希获取铸造结果
     */
    public function scs_getTokenByHash($txHash)
    {
        $param=[
            $txHash,
        ];
        return $this->request("scs_getTokenByHash",$param,1);
    }



    /**
     * 转移nft
     */
    public function transferNft($param)
    {
        $url = 'https://kovan.infura.io/v3/8f762549c8c341388ac03552835a0358';
        // 为了拿到合约方法的编码
        $web3 = new Web3(new HttpProvider(new HttpRequestManager($url, 5)));
        $contract = new Contract($web3->getProvider(), $this->testAbi);
        // 得到合约编码
        // 23b872dd000000000000000000000000c4244f49522c32e6181b759f35be5efa2f19d7f9000000000000000000000000d4ec9ee5613ec88fd2c3855b2c837bd8832b97cf0000000000000000000000000000000000000000000000000000000000000001
        $data = $contract->getData('transferFrom', $param['fromAddress'], $param['toAddress'], $param['tokenId']);
        // value 写死，chainId写死
        $trans = [
            'from'=>$param['fromAddress'],
            'value'=>0x0,
            "nonce"=>$this->scs_getTransactionCount(),
            "gasPrice"=>$this->scs_gasPrice(),
            "gas"=>$this->gasLimit,
            "to"=>$param['contractAddress'],
            "data"     => '0x' . $data,
            "chainId"=> 20180818,
        ];

        $tx = new Transaction($trans);
        $raw = $tx->sign($this->privateKey);
        // 交易如果失败，需要查询为什么，把这个raw和交易哈希发群里问
//        var_dump($raw);

        return $this->request("scs_sendRawTransaction", ['0x' . $raw], 1);
    }


    public function transferDrop($toAddress, $drop)
    {
        // 转移是直接交易，就不用调用合约了
        // value 就是转多少了，chainId写死
        $trans = [
            'from'=>$this->fromAddress,
            'value'=>$drop * pow(10,18),
            "nonce"=>$this->scs_getTransactionCount(),
            "gasPrice"=>$this->scs_gasPrice(),
            "gas"=>$this->gasLimit,
            "to"=>$toAddress,
            "chainId"=> 20180818,
        ];
        $tx = new Transaction($trans);
        $raw = $tx->sign($this->privateKey);
        // 交易如果失败，需要查询为什么，把这个raw和交易哈希发群里问
//        var_dump($raw);

        return $this->request("scs_sendRawTransaction", ['0x' . $raw], 1);
    }

    public function scs_call($param,$blockTag)
    {
        $trans=[
            $param,
            $blockTag,
        ];
        return $this->request("scs_call",$trans,1);
    }

    public function ownerOf($param)
    {
        $url = 'https://kovan.infura.io/v3/8f762549c8c341388ac03552835a0358';
        // 为了拿到合约方法的编码
        $web3 = new Web3(new HttpProvider(new HttpRequestManager($url, 5)));
        $contract = new Contract($web3->getProvider(), $this->testAbi);
        // 得到合约编码
        $data = $contract->getData('ownerOf', $param['tokenId']);

        // https://docs.qq.com/doc/DUEt6R3l5Rk9Ka2x3?_t=1648433458277 3.5.19 scs_call
        $obj=[
            $this->fromAddress,
            $param['contractAddress'],
            "0x10000",
            "",
            "0x0",
            $data,
        ];

        return $this->scs_call($obj,"latest");
    }







    protected $testAbi =
        '[
            {
              "constant": true,
              "inputs": [],
              "name": "totalSupply",
              "outputs": [
                {
                  "name": "",
                  "type": "uint256"
                }
              ],
              "payable": false,
              "stateMutability": "view",
              "type": "function"
            },
            {
              "constant": false,
              "inputs": [
                {
                  "name": "_from",
                  "type": "address"
                },
                {
                  "name": "_to",
                  "type": "address"
                },
                {
                  "name": "_value",
                  "type": "uint256"
                }
              ],
              "name": "transferFrom",
              "outputs": [
                {
                  "name": "success",
                  "type": "bool"
                }
              ],
              "payable": false,
              "stateMutability": "nonpayable",
              "type": "function"
            },
            {
              "anonymous": false,
              "inputs": [
                {
                  "name": "_tokenId",
                  "type": "uint256"
                }
              ],
              "name": "ownerOf",
              "outputs": [
                {
                  "name": "ownerAddress",
                  "type": "address"
                }
              ],
              "payable": false,
              "stateMutability": "nonpayable",
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


}