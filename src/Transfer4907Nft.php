<?php

use strait_chain\StraitChainClient;

require_once __DIR__ . '/strait_chain/StraitChainClient.php';
// 这些参数改成你自己的参数
// 从这里拿https://www.straitchain.com/#/accountCenter/saveSetUp/saveSetUp
$appId = "appId";
$appKey = "appKey";

// 通行证私钥从插件获取
$privateKey = "私钥";
// 账号的地址主页拿，通行证拿也有  https://www.straitchain.com/#/homepage
$fromAddress = "通行证地址";


$straitClient = new StraitChainClient();
$straitClient->setAppId($appId);
$straitClient->setAppKey($appKey);
$straitClient->setPrivateKey($privateKey);
$straitClient->setFromAddress($fromAddress);


// string(126) "{ "error":null, "id":"1", "jsonrpc":"2.0", "result":"0x744fe276bfcf3741e6b450511b2cb5f2f3d600d5d1ac291be4d2956127b15c1d" }"
// 第一步，部署合约，返回合约的部署哈希
//$contractDeployTx = $straitClient->scs_deployContract(10,1);
//---------------------------------------------------------------------------------------------------------------------

//string(102) "{ "error":null, "id":"1", "jsonrpc":"2.0", "result":"0x4eef7d97ab5ceb0a801c986ad6580f68edd4a22a" }"
// 第二部，根据部署哈希获得合约地址
//$contractDeployAddress = $straitClient->scs_contractAddressByHash("0x744fe276bfcf3741e6b450511b2cb5f2f3d600d5d1ac291be4d2956127b15c1d");

//---------------------------------------------------------------------------------------------------------------------


//nftURI： 为json文件或txt文件的在线地址（推荐json,铸造后不可变），图片地址，jpg png啥的
//(仅支持https请求的图片地址)内容为：{"attributes":[],"image":"https://xxxx.com/xxx.png","name":"xx"}

// 第三步，铸造nft，需要先构造一个json属性文件，并能够在线访问
//$mintParam = [
//    'nftName'         => '测试',
//    'cid'             => '-1',
//    'nftURI'          => 'https://cdnstrait.shang-chain.com/default/test.json',
//    'copyright'       => '熵链科技',
//    'issuer'          => '熵链科技',
//    'operator'        => '熵链科技',
//    'remark'          => '',
//    'count'           => '10',
//    'owner'           => $fromAddress,
//    'contractAddress' => '0x4eef7d97ab5ceb0a801c986ad6580f68edd4a22a',
//    'collectSn'       => '-1',
//    'serviceId'       => '',
//];
//string(92) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"8982a7d1a4e944c8b36e03f15b02b9b5"
//}"
//$mintTxHash = $straitClient->scs_nft_mint($mintParam);

//---------------------------------------------------------------------------------------------------------------------


//string(1102) "{ "error":null, "id":"1", "jsonrpc":"2.0", "result":[
// { "hash":"0x7a687da373321e926c2fbf360255a2a71d30c7e16f510e4491cec7ba58e6e137", "tokenId":1 },
// ......
// { "hash":"0xca7f05c1986801995e6e5bcb55d9413c1da29f7a480490308cb807ec84cf9c00", "tokenId":10 }
// ] }"
// 第四步，根据铸造交易哈希获取铸造结果
//$mintDto = $straitClient->scs_getTokenByHash("8982a7d1a4e944c8b36e03f15b02b9b5");

//---------------------------------------------------------------------------------------------------------------------

// 第五步 ，租借nft
//$toAddress = "0x171Fa07F54E730364ad843153e896427110F6ea2";
//$param=[
//    'fromAddress'=>$fromAddress,
//	'contractAddress'=>"0x4eef7d97ab5ceb0a801c986ad6580f68edd4a22a",
//	"tokenId"=>1,
//	'toAddress'=>$toAddress,
//	'expires'=>1666665854
//];
//$straitClient->contract4907setUser($param);
// txHash = '0xec646029d77cfdeef412b5f2fedd2c0b5eaf5faa4bdc64f59e12447d14a16ef4'

// 第六步，查询是否成功
//$response = $straitClient->scs_getTransactionReceipt("0xec646029d77cfdeef412b5f2fedd2c0b5eaf5faa4bdc64f59e12447d14a16ef4");
//// 0x1成功
//var_dump($response->result->status);

// 查租赁的用户地址
//$param=[
//	"tokenId"=>1,
//	"contractAddress"=>"0x4eef7d97ab5ceb0a801c986ad6580f68edd4a22a",
//];
//$address = $straitClient->contract4907userOf($param);
//// 0x171fa07f54e730364ad843153e896427110f6ea2
//var_dump($address);

// 查租赁到期时间
$param = [
	"tokenId" => 1,
	"contractAddress" => "0x4eef7d97ab5ceb0a801c986ad6580f68edd4a22a",
];
$result = $straitClient->contract4907userExpires($param);
echo date('Y-m-d H:i:s', $result);