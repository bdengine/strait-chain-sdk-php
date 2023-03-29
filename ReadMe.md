
# 使用介绍
> StraitChainClient.php 是接口实现
> 
> 文件夹demo下是各个示例
> 
> 接口会返回空值，错误没有处理，要自己处理
> 
> 需要安装gmp扩展，用于依赖加解密处理
> 
> php 版本7.4
> 
> composer 依赖，web3p/ethereum-tx、web3p/ethereum-util、web3p/rlp、web3p/web3.php、chesterlyd/ethereum-client、bitwasp/buffertools、bitwasp/bitcoin、bitwasp/bech32

## 参数介绍
### 链接地址
> private $baseUrl = "https://backend.straitchain.com/webclient/api/develop/straits/action";

### 用户标识，海峡链平台交互识别ID
> protected $appId;

### 用户密钥，海峡链平台交互数据加密的密钥
> protected $appKey;

### 通行证私钥（钱包私钥），海峡链通行证插件
> protected $privateKey;

### 通行证地址（钱包地址）），海峡链通行证插件
> protected $fromAddress;

### 合约执行上限，每个合约执行需要一定的gas费用，执行到上限的时候就会停止执行，以防超过预算
> protected $gasLimit = 150000;

### 默认的费用。每个gas费
> protected $gasPrice = 563000000007;

### 默认合约内容，可以转移
> protected $abi721;
> 
### 租赁合约，租借，就像和房东租房子
> protected $abi4907;

### 批量铸造的，但是哈希相同
> protected $abi1155;
> 
### 数字存证 
> protected $abiDigital;