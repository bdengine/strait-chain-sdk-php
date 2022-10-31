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


//// 第一步，铸造
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
// 8fb51643f6d94401a5ce38d87ec96a05
//$txHash = $straitClient->scsDigitalCollectionMint($param);


// 第二部查询铸造结果
//{
// ["error"]=> NULL
// ["id"]=> string(1) "1"
// ["jsonrpc"]=> string(3) "2.0"
// ["result"]=> array(5)
// {
// [0]=> object(stdClass)#4 (2) { ["hash"]=> string(66) "0xaefb69321b4ca063210d42137223ef053c15280a01f7b35f7adaed522315d4a1" ["tokenId"]=> int(1) }
// [1]=> object(stdClass)#5 (2) { ["hash"]=> string(66) "0xaefb69321b4ca063210d42137223ef053c15280a01f7b35f7adaed522315d4a1" ["tokenId"]=> int(2) }
// [2]=> object(stdClass)#6 (2) { ["hash"]=> string(66) "0xaefb69321b4ca063210d42137223ef053c15280a01f7b35f7adaed522315d4a1" ["tokenId"]=> int(3) }
// [3]=> object(stdClass)#7 (2) { ["hash"]=> string(66) "0xaefb69321b4ca063210d42137223ef053c15280a01f7b35f7adaed522315d4a1" ["tokenId"]=> int(4) }
// [4]=> object(stdClass)#8 (2) { ["hash"]=> string(66) "0xaefb69321b4ca063210d42137223ef053c15280a01f7b35f7adaed522315d4a1" ["tokenId"]=> int(5) }
// }
// }
//$dtoList = $straitClient->scsDigitalCollectionList("8fb51643f6d94401a5ce38d87ec96a05");

//// 第三步，获取方法签名，结果很长很长
//$param=[
//	"from"=>$fromAddress,
//	"privateKey"=>$privateKey,
//	"cid"=>"",
//	"content"=>"存证内容",
//];
//$signData = $straitClient->dcEvidenceSignHex($param);
//
//// 第四步上练
//$txParam=[
//	"serviceId"=>"-1",
//	"cid"=>"",
//	"content"=>"存证内容",
//	"signData"=>'0x' . $signData,
//];
//// 0xc068d40628974b5b1930944f3a516ede95b10f6e0907034b204d2d453c5c4038
//$txHash = $straitClient->scsExistingEvidence($txParam);

//// 第五步
//$param=[
//	"from"=>$fromAddress,
//	"to"=>'0x171Fa07F54E730364ad843153e896427110F6ea2',
//	"tokenId"=>1,
//	"mintTxHash"=>'8fb51643f6d94401a5ce38d87ec96a05' // 第一步铸造返回的哈希
//];
//// 0x264d8400eec684f4e6b7ed461636da0c73e3a458606573349b19cb000de9a872
//$txHash = $straitClient->scsDigitalCollectionTransaction($param);

// 第六步，查询转移结果，0x1为成功
$response = $straitClient->scs_getTransactionReceipt("0x264d8400eec684f4e6b7ed461636da0c73e3a458606573349b19cb000de9a872");
var_dump($response->result->status);