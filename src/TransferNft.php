<!DOCTYPE html>
<html>
<head>
	<title></title>
	<meta charset="utf-8">
</head>
<body>

参数不可以使用对象，参数为空一律填 ""
http_post_json 会空指针，你们要处理，还有报错的error


<?php

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


// string(126) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"0xd27dd6519cec69a6ef784b16f2aba8cf0aac88a7cca5eab183bb8f95fb84cf44"
//}"
// 第一步，部署合约，返回合约的部署哈希
//$contractDeployTx = $straitClient->scs_deployContract(10);

//---------------------------------------------------------------------------------------------------------------------

//string(102) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"0xc9cfba2fd2af30f9c7bbf0b1208d692ba99c3220"
//}"
// 第二部，根据部署哈希获得合约地址
//$contractDeployAddress = $straitClient->scs_contractAddressByHash("0xd27dd6519cec69a6ef784b16f2aba8cf0aac88a7cca5eab183bb8f95fb84cf44");

//---------------------------------------------------------------------------------------------------------------------


//nftURI： 为json文件或txt文件的在线地址（推荐json,铸造后不可变），图片地址，jpg png啥的
//(仅支持https请求的图片地址)内容为：{"attributes":[],"image":"https://xxxx.com/xxx.png","name":"xx"}

// 第三步，铸造nft，需要先构造一个json属性文件，并能够在线访问
//$mintParam = [
//    'nftName'         => '测试',
//    'cid'             => '-1',
//    'nftURI'          => 'http://xxx/xx/xxxx.json',
//    'copyright'       => '熵链科技',
//    'issuer'          => '熵链科技',
//    'operator'        => '熵链科技',
//    'remark'          => '',
//    'count'           => '10',
//    'owner'           => $fromAddress,
//    'contractAddress' => '0xc9cfba2fd2af30f9c7bbf0b1208d692ba99c3220',
//    'collectSn'       => '-1',
//    'serviceId'       => '',
//];
//string(92) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"ff48a89e615b4891ad3f7c95beccdf69"
//}"
//$mintTxHash = $straitClient->scs_nft_mint($mintParam);

//---------------------------------------------------------------------------------------------------------------------


//string(1102) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":[
//		{
//			"hash":"0x5837aec149d5fde18967f795d9489d868d3dd3d01491045d3900c939bcad1d6f",
//			"tokenId":1
//		},
//      .......
//		{
//			"hash":"0xd9f2341f8d0538607b4b5ff5f797a5aef6431e2e2a021ac63de012a9ad506ac7",
//			"tokenId":10
//		}
//	]
//}"
// 第四步，根据铸造交易哈希获取铸造结果
//$mintDto = $straitClient->scs_getTokenByHash("ff48a89e615b4891ad3f7c95beccdf69");

//---------------------------------------------------------------------------------------------------------------------

// 第五步 ，转移nft
//string(126) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"0x9cb48ffdf07bf17e177419d2a4b160bde102804e027239d6e36abc8b423915ab"
//}
//$toAddress = "0xd4eC9ee5613Ec88fd2C3855b2c837Bd8832b97CF";
//$param=[
//    'fromAddress'=>$fromAddress,
//    'toAddress'=>$toAddress,
//    'contractAddress'=>"0xc9cfba2fd2af30f9c7bbf0b1208d692ba99c3220",
//    "tokenId"=>1,
//];
//$straitClient->transferNft($param);

// 第六步，查询是否成功
//$response = $straitClient->scs_getTransactionReceipt("0x9cb48ffdf07bf17e177419d2a4b160bde102804e027239d6e36abc8b423915ab");
//// 0x1成功
//var_dump($response->result->status);

$param=[
  "tokenId"=>1,
  "contractAddress"=>"0xc9cfba2fd2af30f9c7bbf0b1208d692ba99c3220",
];
$straitClient->ownerOf($param);

?>
</body>
</html>
