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

> 查询客户
```php
//通过手机号查询单个客户
$mobile = '13800138000';
echo $ec->queryCustomer($mobile);

//通过crmId批量查询客户
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

| 方法 | 说明 |
| ---- | ---- |
| updateLabel | 修改客户标签(支持批量) |
| queryLabel | 查询客户标签(支持批量) |
| abandonCustomer | 放弃客户(支持批量) |
| customerChangeUser | 变更跟进人(支持批量) |
| getCustomerGroup | 查询客户分组(请求协议错误！???) |
| getTrajectory | 查询客户轨迹 |
| queryCustomer | 分页查询客户信息 |
| getCustomer | 自定义分页查询客户信息 |
| preciseQueryCustomer | 批量查询客户信息 |
| addCustomer | 创建客户 |
| addCustomers | 批量创建客户 |
| combineCustomer | 合并客户 |
| updateCustomer | 修改客户信息 |
| batchUpdateCustomer | 批量修改客户信息 |
| updateStep | 修改客户阶段(支持批量) |
| createDept | 创建部门 |
| editDept | 编辑部门 |
| structure | 获取架构信息 |
| createUser | 创建员工 |
| User | 启用/禁用员工 |
| call | 电话外呼 |
| smsRecord | 短信记录 |
| telRecord | 电话记录 |
| getLabelInfo | 获取标签信息 |

> 更多方法看源码并参考EC开放平台技术文档

- [EC开放平台技术文档](https://open.workec.com/newdoc/)

## License

MIT
