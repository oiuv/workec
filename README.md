<h1 align="center">EC开放平台</h1>

<p align="center">使用API，您可将EC与第三方系统进行数据级别的集成</p>


## 环境需求

- PHP >= 5.6

## 安装

```shell

$ composer require "oiuv/workec"

```

## 使用

```php
use Oiuv\WorkEc\EC;

$ec = new EC('corpId', 'appId', 'appSecret');
```

> 获取部门和员工信息
```php
echo $ec->structure();
```

> 获取指定员工信息
```php
$mobile = '13800138000';

echo $ec->findUserInfoById($mobile);
```

> 批量精确查询客户
```php
//通过手机号查询单个客户
$data = '13800138000';

//通过手机号批量查询客户
$data = ['13800138000', '13900139000'];

//通过crmId批量查询客户
$data = [['crmId' => 123], ['crmId' => 456], ['crmId' => 789]]

echo $test->getCustomer($data);
```

> 创建客户
```php
//更多参数见文档：https://open.workec.com/apidoc/index.html#api-groupCustomer-6
$data = [
    'optUserId' => '123456',
    'f_name'    => '测试API',
    'f_mobile'  => '13800138000',
    'f_channel' => '直接输入',
    'f_memo'    => 'API创建客户'
];

echo $test->addCustomer($data);
```

> 修改客户资料
```php
//更多参数见文档：https://open.workec.com/apidoc/index.html#api-groupCustomer-13
$data = [
    'optUserId' => '123456',
    'crmId'     => '123456789',
    'f_name'    => '陈小萌',
    'f_step'    => 4
];

echo $test->updateCustomer($data);
```

> 导出历史电话记录
```php
//更多参数见文档：https://open.workec.com/apidoc/index.html#api-groupContactRecode-28
$data = [
    'year'  => 2018,
    'month' => 05,
];

echo $test->telRecordHistory($data);
```
> 更多方法看源码并参考EC开放平台技术文档

- [EC开放平台技术文档](https://open.workec.com/apidoc/index.html)

## License

MIT
