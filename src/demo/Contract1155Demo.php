<?php


use strait_chain\StraitChainClient;

require_once __DIR__ . '/../strait_chain/StraitChainClient.php';
// 这些参数改成你自己的参数
// 从这里拿https://www.straitchain.com/#/accountCenter/saveSetUp/saveSetUp
$appId = "Ist8KOqm";
$appKey = "8d065964ab77cfa1917bdafa6c27e5dd605590ed";

// 通行证私钥从插件获取
$privateKey = "0x09f51b8fd9e4124e1b80e4ffd475a5a542a438177fed9d4f10d626958e16b1da";
// 账号的地址主页拿，通行证拿也有  https://www.straitchain.com/#/homepage
$fromAddress = "0xc4244f49522c32e6181b759f35be5efa2f19d7f9";


$straitClient = new StraitChainClient();
$straitClient->setUrl("http://192.168.80.15/strait-chain-client-test/api/develop/straits/action");
$straitClient->setAppId($appId);
$straitClient->setAppKey($appKey);
$straitClient->setPrivateKey($privateKey);
$straitClient->setFromAddress($fromAddress);


// string(126) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"0x7ae0bbf22fae4f388bece5453825607a937c80c377a30214e8bf597c8378b641"
//}"
// 第一步，部署合约，返回合约的部署哈希
//$contractDeployTx = $straitClient->scs_deployContract(10,2);

//---------------------------------------------------------------------------------------------------------------------

//string(102) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"0x2e1200354605b9e6b5cafb7e7b67a74ad940a86a"
//}"
// 第二部，根据部署哈希获得合约地址
//$contractDeployAddress = $straitClient->scs_contractAddressByHash($contractDeployTx);
//$contractDeployAddress = $straitClient->scs_contractAddressByHash("0x7ae0bbf22fae4f388bece5453825607a937c80c377a30214e8bf597c8378b641");

//---------------------------------------------------------------------------------------------------------------------


//nftURI： 为json文件或txt文件的在线地址（推荐json,铸造后不可变），图片地址，jpg png啥的
//(仅支持https请求的图片地址)内容为：{"attributes":[],"image":"https://xxxx.com/xxx.png","name":"xx"}

// 第三步，铸造nft，需要先构造一个json属性文件，并能够在线访问
//$mintParam = [
//    'nftName'         => '测试',
//    'cid'             => '-1',
//    'nftURI'          => 'https://xxx/xx/xxxx.json',
//    'copyright'       => '熵链科技',
//    'issuer'          => '熵链科技',
//    'operator'        => '熵链科技',
//    'remark'          => '',
//    'count'           => '10',
//    'owner'           => $fromAddress,
//    'contractAddress' => '0x2e1200354605b9e6b5cafb7e7b67a74ad940a86a',
//    'collectSn'       => '-1',
//    'serviceId'       => '',
//];
//string(169) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":{
//		"count":10,
//		"hash":"0x669cb83849655963b0b904578b7bd64513525a087eaa0993ef6d89c505aaadbb",
//		"tokenId":1
//	}
//}"
//$mintTxHash = $straitClient->scs1155NftMint($mintParam);

//---------------------------------------------------------------------------------------------------------------------

// 第四步 ，转移nft
//string(126) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"0x7fb4761213f167f51c67cda5ab799149c9997a05a7c00b568db3e0b148f5bec7"
//}
//$toAddress = "0xd4eC9ee5613Ec88fd2C3855b2c837Bd8832b97CF";
//$param=[
//    'fromAddress'=>$fromAddress,
//    'toAddress'=>$toAddress,
//    'contractAddress'=>"0x2e1200354605b9e6b5cafb7e7b67a74ad940a86a",
//    "tokenId"=>1,
//	// 转移个数，转多少个填多少个
//    "amount"=>1,
//	// data没有可以不填
//	"data"=>''
//];
//string(126) "{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"0x8e0b81fa6e3b8d8289866b169354a9032b907ba10c66c928a44ede288835357e"
//}"
//$straitClient->transfer1155Nft($param);

// 第五步，查询是否成功
//$response = $straitClient->scs_getTransactionReceipt("0x8e0b81fa6e3b8d8289866b169354a9032b907ba10c66c928a44ede288835357e");
// 0x1成功
//var_dump($response->result->status);

// 查询账户底下，多少个nft
//$param=[
//    'account'=>$toaddress,
//    'fromAddress'=>$fromAddress,
//    'contractAddress'=>"0x2e1200354605b9e6b5cafb7e7b67a74ad940a86a",
//    'tokenId'=>1
//];
//$result = $straitClient->contract1155BalanceOf($param);
//var_dump($result);

// 批量查询账户底下，多少个nft
//$param=[
//    'accounts'=>[0x1,0x1,0x1,0x1,0x1,0x1],
//    'fromAddress'=>$fromAddress,
//    'contractAddress'=>"0x2e1200354605b9e6b5cafb7e7b67a74ad940a86a",
//    'tokenIds'=>[1,2,3,4,5,6]
//];
//$straitClient->contract1155BalanceOfBatch($param);

// 查询铸造信息
$param=[
    'fromAddress'=>$fromAddress,
    'contractAddress'=>"0x2e1200354605b9e6b5cafb7e7b67a74ad940a86a",
    'tokenId'=>1
];
$result = $straitClient->contract1155VerificationNft($param);
// tokenId
var_dump($result[0]->{'value'});
// ownerAddress 拥有者地址
var_dump($result[1]);
// 总数
var_dump($result[2]->{'value'});
// 附加数据
foreach ($result[3] as $item){
	var_dump($item);
}