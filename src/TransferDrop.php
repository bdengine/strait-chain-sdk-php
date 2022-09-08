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


// 1drop = 1人名币 = 1 x 10 ^ 18 gas，这里参数可以写小数，最小到分，即0.01元
// 只能开发者转给普通用户，即海峡链注册的开发账号，发给你们生成的客户钱包地址
//{
//	"error":null,
//	"id":"1",
//	"jsonrpc":"2.0",
//	"result":"0xb245b70c805294ed646887cc73e4e03f33c438da888f5dd6707ba5c5cbdc4f1f"
//}
//$toAddress='0x171Fa07F54E730364ad843153e896427110F6ea2';
//$drop = 1;
//$straitClient->transferDrop($toAddress,$drop);

// 0x1成功
$straitClient->scs_getTransactionReceipt("0xb245b70c805294ed646887cc73e4e03f33c438da888f5dd6707ba5c5cbdc4f1f");