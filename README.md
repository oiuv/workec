<h1 align="center">EC开放平台</h1>

<p align="center">使用API，您可将EC与第三方系统进行数据级别的集成</p>


## 环境需求

- PHP >= 5.6

## 安装

```shell
composer require "oiuv/workec"
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

> 查询客户
```php
// 通过条件查询客户列表
echo $ec->queryList(['name'=>'测试']);

// 通过手机号查询单个客户
$mobile = 13800138000;
echo $ec->getCustomer($mobile);

// 通过crmId批量查询客户
$crmIds = '12345,14336,13093';
echo $ec->preciseQueryCustomer($crmIds);
```

> 创建客户
```php
// 单个创建；参数：$optUserId, $name, $mobile, $followUserId
echo $ec->addCustomer(12345, '测试API', '13800138000', 67890);
// 批量创建
$data = [
    'optUserId' => $optUserId,
    'list' => [
        [
            'name' => $name,
            'mobile' => $mobile,
            'followUserId' => $followUserId,
            'channelId' => $channelId,
            'memo' => $memo,
        ],
        [
            'name' => $name,
            'mobile' => $mobile,
            'followUserId' => $followUserId,
            'channelId' => $channelId,
            'memo' => $memo,
        ],
        [
            'name' => $name,
            'mobile' => $mobile,
            'followUserId' => $followUserId,
            'channelId' => $channelId,
            'memo' => $memo,
        ]
    ]
];
echo $ec->addCustomers($data);
```

> 修改客户资料
```php
// 修改单个用户
$data = [
    'optUserId' => 123456,
    'crmId'     => 123456789,
    'name'    => '陈小萌',
    'mobile'    => '13800138000'
];
echo $ec->updateCustomer($data);
// 批量修改用户
$list =[
    [
        'optUserId' =>12345,
        'crmId'=>1234567,
        'name'=>'用户1'
    ],
    [
        'optUserId' =>12345,
        'crmId'=>1234568,
        'name'=>'用户2'
    ],
    [
        'optUserId' =>67890,
        'crmId'=>1234569,
        'name'=>'用户3'
    ]
];
echo $ec->batchUpdateCustomer($list);
```
### 方法列表

本接口提供的所有方法请见以下文档，对未封装的接口，可自己调用`client()`方法实现。方法返回结果说明请从[EC开放平台技术文档](https://open.workec.com/newdoc/)查询。

- [EC开放平台API接口](https://api.oiuv.cn/workec/)

### 接口文档

- [EC开放平台技术文档](https://open.workec.com/newdoc/)
- [业务返回码说明](https://open.workec.com/newdoc/doc/1iqT8Bqqm)
- [系统字段对照表](https://open.workec.com/newdoc/doc/1jRy6T9uy)
- [api 认证信息查看](https://open.workec.com/newdoc/doc/7wQRq1umF)

## License

MIT
