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
$straitClient->setAppId($appId);
$straitClient->setAppKey($appKey);
$straitClient->setPrivateKey($privateKey);
$straitClient->setFromAddress($fromAddress);
$straitClient->setUrl("http://192.168.80.15/strait-chain-client-test/api/develop/straits/action");
