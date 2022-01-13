<h1 align="center">EC开放平台</h1>

<p align="center">使用API，您可将EC与第三方系统进行数据级别的集成</p>

## 环境需求

- PHP >= 7.1.3

## 安装

```shell
composer require "oiuv/workec"
```

如果你的项目PHP版本低于v7.1.3，可安装 v2.1.1 版。

```shell
composer require oiuv/workec 2.1.1
```

## 使用

### 通过composer自动加载

```php
require __DIR__ . '/vendor/autoload.php';

use Oiuv\WorkEc\EC;

$ec = new EC('corpId', 'appId', 'appSecret');
// 获取部门和员工信息
echo $ec->structure();
```

### 在Laravel框架中使用

在`.env`中增加以下配置：

```
EC_CORP_ID=XXXXXXXX
EC_APP_ID=XXXXXXXXX
EC_APP_SECRET=XXXXX
```

在`config/services.php`中增加以下配置：

```php
'workec' => [
    'corp_id' => env('EC_CORP_ID'),
    'app_id' => env('EC_APP_ID'),
    'app_secret' => env('EC_APP_SECRET'),
],
```

方法参数注入的方式调用

```php
use Oiuv\WorkEc\EC;

public function show(EC $ec)
{
    // 获取部门和员工信息
    return $ec->structure();
}
```

使用Facade的方式调用

```php
public function show()
{
    // 获取部门和员工信息
    return WorkEC::structure();
}
```

---

### 示例

> 获取配置信息

```php
// 获取部门和员工信息
echo $ec->structure();
// 获取客户来源信息
echo $ec->getChannelSource();
// 获取标签信息
echo $ec->getLabelInfo();
// 获取全国地区信息
echo $ec->getAreas();
```

> 查询客户

```php
// 通过条件查询客户列表
echo $ec->queryList(['name'=>'测试']);
echo $ec->queryList(['mobile'=>'13800138000']);

// 通过手机号查询客户
$mobile = 13800138000;
echo $ec->getCustomer($mobile);

// 批量获取客户列表
echo $ec->getCustomers();
echo $ec->queryCustomers();

// 通过crmId批量查询客户
$crmIds = '12345,14336,13093';
echo $ec->preciseQueryCustomer($crmIds);

// 判断客户是否存在
$mobile = 13800138000;
echo $ec->queryExist($mobile);
echo $ec->queryExist($mobile, 0); //只查询数量不返回客户资料
```

> 创建客户

```php
// 单个创建
echo $ec->addCustomer($optUserId, $name, $mobile);
// 批量创建

$list => [
    [
        'name' => $name1,
        'mobile' => $mobile1,
        'followUserId' => $followUserId,
        'channelId' => $channelId,
        'memo' => $memo,
    ],
    [
        'name' => $name2,
        'mobile' => $mobile2,
        'followUserId' => $followUserId,
        'channelId' => $channelId,
        'memo' => $memo,
    ],
    [
        'name' => $name3,
        'mobile' => $mobile3,
        'followUserId' => $followUserId,
        'channelId' => $channelId,
        'memo' => $memo,
    ]
];
echo $ec->addCustomers($optUserId, $list);
```

> 修改客户资料

```php
// 修改单个用户
$data = [
    'name'   => '陈小萌',
    'mobile' => '13800138000'
];
echo $ec->updateCustomer(123456, 123456789, $data); // 操作员ID，客户ID，要修改的资料
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

> 电话外呼

```php
echo $ec->call($userid, $phone);
```

> 问题和需求反馈可联系QQ 7300637

### 方法列表

本接口提供的所有方法请见以下文档，对未封装的接口，可自己调用`client()`方法实现。

- [EC开放平台API接口](https://api.oiuv.cn/workec/)

各方法返回值参数很复杂，如有问题请从**EC开放平台技术文档**查询。

### 接口文档

- [EC开放平台技术文档](https://open.workec.com/newdoc/)
- [业务返回码说明](https://open.workec.com/newdoc/doc/1iqT8Bqqm)
- [系统字段对照表](https://open.workec.com/newdoc/doc/1jRy6T9uy)
- [api 认证信息查看](https://open.workec.com/newdoc/doc/7wQRq1umF)

## License

MIT
