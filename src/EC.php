<?php
/**
 * EC开放平台API
 * @author oiuv <i@oiuv.cn>
 * @version 1.1.2
 * @link https://open.workec.com/apidoc/index.html
 */

namespace Oiuv\WorkEc;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class EC
{
    /**
     * 公司ID
     * @var int
     */
    private $corpId;

    /**
     * EC APP ID
     * @var double
     */
    private $appId;

    /**
     * app_secret
     * @var String
     */
    private $appSecret;

    /**
     * HTTP 客户端
     */
    private $client;

    public function __construct($corpId = '', $appId = '', $appSecret = '')
    {
        $this->client = new Client(['base_uri' => 'https://open.workec.com/']);

        if ($corpId)
            $this->corpId = trim($corpId);

        if ($appId) {
            $this->appId = trim($appId);
        }

        if ($appSecret) {
            $this->appSecret = trim($appSecret);
        }
    }

    public function client($method, $uri, $data = [])
    {
        try {
            $response = $this->client->request($method, $uri, [
                'headers' => [
                    'authorization' => $this->accessToken(),
                    'corp_id'       => $this->corpId,
                    'cache-control' => 'no-cache'
                ],
                'json'    => $data,
            ]);
            return $response->getBody()->getContents();
        } catch (GuzzleException $exception) {
            return $exception->getMessage();
        }
    }

    /**
     *  获取access_token
     */
    public function accessToken()
    {
        $response = $this->client->post('auth/accesstoken', ['json' => ['appId' => $this->appId, 'appSecret' => $this->appSecret]]);
        //return $response->getBody()->getContents();
        $result = json_decode($response->getBody()->getContents());
        if ($result->errCode == 200)
            return $result->data->accessToken;
        else
            //return $result->errMsg;
            return false;
    }

    /**
     *  获取部门和员工信息
     */
    public function structure()
    {
        $response = $this->client('get', 'user/structure');
        return $response;
    }

    /**
     *  获取指定员工信息
     * @param String $account 用户账号(手机号码)
     * @param String $userId 用户ID
     * $userId和$account必须填写一个，如果都填，以$userId为准
     * @return string
     */
    public function findUserInfoById($account = '', $userId = '')
    {
        $data = [
            'userId'  => $userId,
            'account' => $account
        ];
        $response = $this->client('get', 'user/findUserInfoById', $data);
        return $response;
    }

    /**
     * 创建客户
     */
    public function addCustomer($data)
    {
        $response = $this->client('post', 'customer/addCustomer', $data);
        return $response;
    }

    /**
     * 批量创建客户
     */
    public function createCustomer($data)
    {
        $response = $this->client('post', 'customer/create', $data);
        return $response;
    }

    /**
     * 批量精确查询客户
     * @param array | String $data 参数可以是手机号、手机号数组，或crmId二维数组：[['crmId' => 123], ['crmId' => 456], ['crmId' => 789]]
     * @return string
     */
    public function getCustomer($data)
    {
        if (is_array($data)) {
            if (array_key_exists('crmId', $data[0]))
                $data = [
                    'list' => $data
                ];
            else {
                $data = array_map(function ($mobile) {
                    return [
                        'mobile' => $mobile
                    ];
                }, $data);
                $data = [
                    'list' => $data
                ];
            }
        } else
            $data = [
                'list' => [
                    ['mobile' => $data]
                ]
            ];
        $response = $this->client('get', 'customer/get', $data);
        return $response;
    }

    /**
     *  根据条件分页查询客户
     */
    public function rangeQueryCustomer($data)
    {
        $response = $this->client('post', 'customer/rangeQueryCustomer', $data);
        return $response;
    }

    /**
     * 获取自定义字段信息
     * @param Int $type 按资料类型传对应值： 1 客户资料 2 公司资料
     * @return string
     */
    public function getCustomFieldMapping($type = 1)
    {
        $data = [
            'type' => $type
        ];
        $response = $this->client('post', 'customer/getCustomFieldMapping', $data);
        return $response;
    }

    /**
     * 获取员工客户库分组信息
     * @param int $userId 员工ID
     * @return string
     */
    public function getCustomerGroup($userId)
    {
        $data = [
            'userId' => $userId
        ];
        $response = $this->client('post', 'customer/getCustomerGroup', $data);
        return $response;
    }

    /**
     * 修改客户资料
     */
    public function updateCustomer($data)
    {
        $response = $this->client('post', 'customer/updateCustomer', $data);
        return $response;
    }

    /**
     * 获取客户来源信息
     */
    public function getChannelSource()
    {
        $response = $this->client('get', 'customer/getChannelSource');
        return $response;
    }

    /**
     * 变更客户跟进人
     */
    public function changeCrmFollowUser($data)
    {
        $response = $this->client('post', 'customer/changeCrmFollowUser', $data);
        return $response;
    }

    /**
     * 放弃客户
     */
    public function abandonCustomer($data)
    {
        $response = $this->client('post', 'customer/abandon', $data);
        return $response;
    }

    /**
     * 获取删除的客户
     * @param String $startTime 查询删除客户的开始时间,格式yyyy-MM-dd HH:mm:ss
     * @param String $endTime 查询删除客户的截止时间,格式yyyy-MM-dd HH:mm:ss, 与startTime最大间隔7天
     * @param String $lastId 根据此参数来进行翻页。上一次请求得到的最后一条记录中的id，初始值可为""
     * @return string
     */
    public function delcrms($startTime = '', $endTime = '', $lastId = '')
    {
        $data = [
            "startTime" => $startTime,
            "endTime"   => $endTime,
            "lastId"    => $lastId
        ];
        $response = $this->client('post', 'customer/delcrms', $data);
        return $response;
    }

    /**
     * 获取员工签到记录
     */
    public function getCrmVisitDetails($data)
    {
        $response = $this->client('post', 'customer/getCrmVisitDetails', $data);
        return $response;
    }

    /**
     * 创建标签分组
     * @param int $userId 操作人ID
     * @param String $name 标签分组名
     * @param String $color 分组颜色 默认值为 c1,取值范围[c1~c20]
     * @param int $type 分组类型 默认值为0 取值： 0 代表此分组的标签可以多选 1 代表此分组的标签只能单选
     * @return string
     */
    public function addLabelGroup($userId, $name, $color = 'c1', $type = 0)
    {
        $data = [
            'name'   => $name,
            'type'   => $type,
            'color'  => $color,
            'userId' => $userId
        ];
        $response = $this->client('post', 'label/addLabelGroup', $data);
        return $response;
    }

    /**
     * 创建标签
     * @param String $name 标签名
     * @param String $groupValue 分组id或者分组名
     * @param int $userId 操作人ID
     * @return string
     */
    public function addLabel($name, $groupValue, $userId)
    {
        $data = [
            'name'        => $name,
            '$groupValue' => $groupValue,
            'userId'      => $userId
        ];
        $response = $this->client('post', 'label/addLabel', $data);
        return $response;
    }

    /**
     * 批量修改客户标签
     */
    public function updateLabel($data)
    {
        $data = [
            'list' => $data
        ];
        $response = $this->client('post', 'label/update', $data);
        return $response;
    }

    /**
     * 获取标签信息
     * @param String $groupValue 分组id或者分组名
     * @return string
     */
    public function getLabelInfo($groupValue = '')
    {
        $data = [
            'groupValue' => $groupValue
        ];
        $response = $this->client('post', 'label/getLabelInfo', $data);
        return $response;
    }

    /**
     * 批量添加跟进记录
     */
    public function saveUserTrajectory($data)
    {
        $data = [
            'list' => $data
        ];
        $response = $this->client('post', 'trajectory/saveUserTrajectory', $data);
        return $response;
    }

    /**
     * 导出跟进记录
     */
    public function findUserTrajectory($data)
    {
        $response = $this->client('post', 'trajectory/findUserTrajectory', $data);
        return $response;
    }

    /**
     * 导出历史跟进记录
     */
    public function findHistoryUserTrajectory($data)
    {
        $response = $this->client('post', 'trajectory/findHistoryUserTrajectory', $data);
        return $response;
    }

    /**
     * 导出电话记录
     */
    public function telRecord($data)
    {
        $response = $this->client('post', 'record/telRecord', $data);
        return $response;
    }

    /**
     * 导出历史电话记录
     */
    public function telRecordHistory($data)
    {
        $response = $this->client('post', 'record/telRecordHistory', $data);
        return $response;
    }

    /**
     * 导出短信记录
     */
    public function sendSms($data)
    {
        $response = $this->client('post', 'record/sendSms', $data);
        return $response;
    }

    /**
     * 导出历史短信记录
     */
    public function sendSmsHistory($data)
    {
        $response = $this->client('post', 'record/sendSmsHistory', $data);
        return $response;
    }

    /**
     * 添加电话记录
     */
    public function addTelRecord($data)
    {
        $data = [
            'list' => $data
        ];
        $response = $this->client('post', 'record/addTelRecord', $data);
        return $response;
    }

    /**
     * 查询客户轨迹
     */
    public function getTrajectory($data)
    {
        $response = $this->client('post', 'customer/getTrajectory', $data);
        return $response;
    }

    /**
     * 获取销售金额字段信息
     */
    public function getSalesFieldMapping()
    {
        $response = $this->client('get', 'sales/getSalesFieldMapping');
        return $response;
    }

    /**
     * 创建销售金额
     */
    public function addSales($data)
    {
        $response = $this->client('post', 'sales/addSales', $data);
        return $response;
    }

    /**
     * 修改销售金额
     */
    public function updateSales($data)
    {
        $response = $this->client('post', 'sales/updateSales', $data);
        return $response;
    }

    /**
     * 更新销售金额状态
     */
    public function updateStatus($data)
    {
        $response = $this->client('post', 'sales/updateStatus', $data);
        return $response;
    }

    /**
     * 查询销售金额列表
     * @param array $data
     */
    public function getSales($data)
    {
        $response = $this->client('post', 'sales/getSales', $data);
        return $response;
    }

    /**
     * 查询销售金额详情
     * @param int $saleId 销售金额的id,在创建销售金额时返回的id，或者查询列表得到id。
     * @return string
     */
    public function getSalesDetail($saleId)
    {
        $data = [
            'saleId' => $saleId
        ];
        $response = $this->client('post', 'sales/getSalesDetail', $data);
        return $response;
    }
}
