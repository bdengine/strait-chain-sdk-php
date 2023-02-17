<?php

/**
 * 数字版权存证服务
 */

use strait_chain\StraitChainClient;

require_once __DIR__ . '/strait_chain/StraitChainClient.php';
// 这些参数改成你自己的参数
// 从这里拿https://www.straitchain.com/#/accountCenter/saveSetUp/saveSetUp
$appId = "Ist8KOqm";
$appKey = "8d065964ab77cfa1917bdafa6c27e5dd605590ed";

// 通行证私钥从插件获取
$privateKey = "0x09f51b8fd9e4124e1b80e4ffd475a5a542a438177fed9d4f10d626958e16b1da";
// 账号的地址主页拿，通行证拿也有  https://www.straitchain.com/#/homepage
$fromAddress = "0xc4244f49522c32e6181b759f35be5efa2f19d7f9";


$straitClient = new StraitChainClient();
$straitClient->setAppId($appId);
$straitClient->setAppKey($appKey);
$straitClient->setPrivateKey($privateKey);
$straitClient->setFromAddress($fromAddress);
$straitClient->setUrl("http://192.168.80.15/strait-chain-client-test/api/develop/straits/action");


// 数字版权存证

// 第一步，铸造
//$param=[
//	"name"=>"数字藏品名",
//	"number"=>"shang-chain-test-digital-collection-1",
//	"nftUri"=>"https://cdnstrait.shang-chain.com/default/test.json",
//	"copyright"=>"熵链科技",
//	"issuer"=>"熵链科技",
//	"operator"=>"熵链科技",
//	"remark"=>"备注",
//	"count"=>"5",
//	"owner"=>$fromAddress,
//];
//string(92) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"a6ef716434ba4dc8b9a6850dbe8e1663"
//}"
//$txHash = $straitClient->scsDigitalCollectionMint($param);


// 第二部查询铸造结果
//string(581) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":[
//		{
//			"hash":"0x3ca68b390d6028b3f8d2d8d505c4c397ec98874d33a8da51a57c52ff82b9b7dd",
//			"tokenId":1
//		},
//      ...
//		{
//			"hash":"0x3ca68b390d6028b3f8d2d8d505c4c397ec98874d33a8da51a57c52ff82b9b7dd",
//			"tokenId":5
//		}
//	]
//}"

//$dtoList = $straitClient->scsDigitalCollectionList("a6ef716434ba4dc8b9a6850dbe8e1663");

//// 第三步
//$param=[
//	"from"=>$fromAddress,
//	"to"=>'0x171Fa07F54E730364ad843153e896427110F6ea2',
//	"tokenId"=>1,
//	"mintTxHash"=>'a6ef716434ba4dc8b9a6850dbe8e1663' // 第一步铸造返回的哈希
//];
//string(126) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"0x703df89f722143b80073b57063ebe9efcb71fc045086b49f7c8470eea9012d0d"
//}"
//$txHash = $straitClient->scsDigitalCollectionTransaction($param);

// 第四步，查询转移结果，0x1为成功
$response = $straitClient->scs_getTransactionReceipt("0x703df89f722143b80073b57063ebe9efcb71fc045086b49f7c8470eea9012d0d");
var_dump($response->result->status);