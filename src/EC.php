<?php
/**
 * EC开放平台API.
 *
 * @author oiuv <i@oiuv.cn>
 *
 * @version 2.0.2
 *
 * @link https://open.workec.com/newdoc/
 */

namespace Oiuv\WorkEc;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class EC
{
    /**
     * 公司ID.
     *
     * @var int
     */
    private $corpId;

    /**
     * EC APP ID.
     *
     * @var float
     */
    private $appId;

    /**
     * app_secret.
     *
     * @var string
     */
    private $appSecret;

    /**
     * HTTP 客户端.
     */
    private $client;

    public function __construct($corpId = '', $appId = '', $appSecret = '')
    {
        $this->client = new Client(['base_uri' => 'https://open.workec.com/v2/']);

        if ($corpId) {
            $this->corpId = trim($corpId);
        }

        if ($appId) {
            $this->appId = trim($appId);
        }

        if ($appSecret) {
            $this->appSecret = trim($appSecret);
        }
    }

    public function client($method, $uri, $data = [])
    {
        // 获取当前时间戳
        $timeStamp = time() * 1000;
        // 获取签名
        $sign = $this->getSign($timeStamp, $this->appId, $this->appSecret);

        try {
            $response = $this->client->request($method, $uri, [
                'headers' => [
                    'Content-Type'   => 'application/json; charset=utf-8',
                    'X-Ec-Cid'       => $this->corpId,
                    'X-Ec-Sign'      => $sign,
                    'X-Ec-TimeStamp' => $timeStamp,
                ],
                'json'    => $data,
            ]);

            return $response->getBody()->getContents();
        } catch (GuzzleException $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * 签名算法.
     *
     * @param int    $timeStamp
     * @param string $appId
     * @param string $appSecret
     *
     * @return string 返回签名数据
     */
    public function getSign($timeStamp, $appId, $appSecret)
    {
        $sign = "appId={$appId}&appSecret={$appSecret}&timeStamp={$timeStamp}";

        return strtoupper(md5($sign));
    }

    /**********客户相关接口**********/

    /**
     * 修改客户标签(支持批量).
     */
    public function updateLabel($optUserId, $crmIds, $labels = null, $type = 0)
    {
        $data = [
            'optUserId' => $optUserId,
            'crmIds'    => $crmIds,
            'labels'    => $labels,
            'type'      => $type,
        ];
        $response = $this->client('post', 'label/update', $data);

        return $response;
    }

    /**
     * 查询客户标签(支持批量).
     *
     * @param string $crmIds 客户id列表，多个id使用英文逗号分隔
     */
    public function queryLabel($crmIds)
    {
        $data = [
            'crmIds' => $crmIds,
        ];
        $response = $this->client('post', 'customer/queryLabel', $data);

        return $response;
    }

    /**
     * 放弃客户(支持批量).
     */
    public function abandonCustomer($optUserId, $crmIds, $type = 1)
    {
        $data = [
            'optUserId' => $optUserId,
            'crmIds'    => $crmIds,
            'type'      => $type,
        ];
        $response = $this->client('post', 'customer/change/abandon', $data);

        return $response;
    }

    /**
     * 变更跟进人(支持批量).
     */
    public function customerChangeUser($optUserId, $crmIds, $followUserId)
    {
        $data = [
            'optUserId'    => $optUserId,
            'crmIds'       => $crmIds,
            'followUserId' => $followUserId,
        ];
        $response = $this->client('post', 'customer/change/user', $data);

        return $response;
    }

    /**
     * 查询客户分组(请求协议错误！???).
     */
    public function getCustomerGroup($userId)
    {
        $data = [
            'userId' => $userId,
        ];
        $response = $this->client('get', 'customer/getCustomerGroup', $data);

        return $response;
    }

    /**
     * 查询客户轨迹.
     */
    public function getTrajectory($startTime, $endTime, $trajectoryType, $crmIds = '', $lastId = 0, $lastTime = '', $pageSize = 200)
    {
        $data = [
            'date' => [
                'endTime'   => $endTime,
                'startTime' => $startTime,
            ],
            'trajectoryType' => $trajectoryType,
            'crmIds'         => $crmIds,
            'lastId'         => $lastId,
            'lastTime'       => $lastTime,
            'pageSize'       => $pageSize,
        ];
        $response = $this->client('post', 'customer/getTrajectory', $data);

        return $response;
    }

    /**
     * 分页查询客户信息.
     */
    public function queryCustomer($mobile = null, $email = null, $step = null, $labelIds = null, $followUserId = null, $publicPondId = null, $modifyTime = null, $contactTime = null, $createTime = null, $sortField = null, $sortType = 'asc', $pageSize = 200, $pageNo = 1)
    {
        $data = [
            'mobile'       => $mobile,
            'email'        => $email,
            'step'         => $step,
            'labelIds'     => $labelIds,
            'followUserId' => $followUserId,
            'publicPondId' => $publicPondId,
            'modifyTime'   => $modifyTime,
            'contactTime'  => $contactTime,
            'createTime'   => $createTime,
            'orderBy'      => [
                'sortField' => $sortField,
                'sortType'  => $sortType,
            ],
            'pageInfo' => [
                'pageSize' => $pageSize,
                'pageNo'   => $pageNo,
            ],
        ];
        $response = $this->client('post', 'customer/query', $data);

        return $response;
    }

    /**
     * 自定义分页查询客户信息.
     */
    public function getCustomer(array $detail = [], $sortField = null, $sortType = 'desc', $pageSize = 200, $pageNo = 1)
    {
        $data = $detail;
        $data += [
            'orderBy'      => [
                'sortField' => $sortField,
                'sortType'  => $sortType,
            ],
            'pageInfo' => [
                'pageSize' => $pageSize,
                'pageNo'   => $pageNo,
            ],
        ];
        $response = $this->client('post', 'customer/query', $data);

        return $response;
    }

    /**
     * 批量查询客户信息.
     */
    public function preciseQueryCustomer($crmIds)
    {
        $data = [
            'crmIds' => $crmIds,
        ];
        $response = $this->client('post', 'customer/preciseQuery', $data);

        return $response;
    }

    /**
     * 创建客户.
     */
    public function addCustomer($optUserId, $name, $mobile, $followUserId = null, $channelId = null, $memo = '', array $detail = [])
    {
        $data = [
            'optUserId' => $optUserId,
            'list'      => [
                [
                    'name'         => $name,
                    'mobile'       => $mobile,
                    'followUserId' => $followUserId,
                    'channelId'    => $channelId,
                    'memo'         => $memo,
                ],
            ],
        ];
        $data['list'][0] += $detail;

        $response = $this->client('post', 'customer/addCustomer', $data);

        return $response;
    }

    /**
     * 批量创建客户.
     */
    public function addCustomers($optUserId, array $list)
    {
        $data = [
            'optUserId' => $optUserId,
            'list'      => $list,
        ];
        $response = $this->client('post', 'customer/addCustomer', $data);

        return $response;
    }

    /**
     * 合并客户.
     */
    public function combineCustomer($optUserId, array $detail)
    {
        $data = [
            'optUserId' => $optUserId,
        ];
        $data += $detail;

        $response = $this->client('post', 'customer/combine', $data);

        return $response;
    }

    /**
     * 修改客户信息.
     */
    public function updateCustomer($optUserId, $crmId, array $detail)
    {
        $data = [
            'optUserId' => $optUserId,
            'crmId'     => $crmId,
        ];
        $data += $detail;

        $response = $this->client('post', 'customer/updateCustomer', $data);

        return $response;
    }

    /**
     * 批量修改客户信息.
     */
    public function batchUpdateCustomer(array $data)
    {
        $response = $this->client('post', 'customer/batchUpdateCustomer', $data);

        return $response;
    }

    /**
     * 修改客户阶段(支持批量).
     */
    public function updateStep($optUserId, $crmIds, $step)
    {
        $data = [
            'optUserId' => $optUserId,
            'crmIds'    => $crmIds,
            'step'      => $step,
        ];
        $response = $this->client('post', 'step/update', $data);

        return $response;
    }

    /**********异步任务相关接口**********/

    /**
     * 创建任务.
     */
    public function createTask($taskName, $type, array $detail)
    {
        $data = [
            'taskName' => $taskName,
            'type'     => $type,
        ];
        $data += $detail;

        $response = $this->client('post', 'asynchronization/create', $data);

        return $response;
    }

    /**
     * 查询任务.
     */
    public function queryTask($taskId, $taskStatus)
    {
        $data = [
            'taskId'     => $taskId,
            'taskStatus' => $taskStatus,
        ];
        $response = $this->client('post', 'asynchronization/query', $data);

        return $response;
    }

    /**********机器人相关接口**********/

    /**
     * 增加任务.
     */
    public function addTask($title, $type, $userId, $time, $finishTime, $finish, $total, $craft, $robotId)
    {
        $data = [
            'title'      => $title,
            'type'       => $type,
            'userId'     => $userId,
            'time'       => $time,
            'finishTime' => $finishTime,
            'finish'     => $finish,
            'total'      => $total,
            'craft'      => $craft,
            'robotId'    => $robotId,
        ];

        $response = $this->client('post', 'robot/addtask', $data);

        return $response;
    }

    /**
     * 增加任务记录.
     */
    public function addTaskRecord($data)
    {
        $response = $this->client('post', 'robot/addtaskrecord', $data);

        return $response;
    }

    /**
     * 更新任务.
     */
    public function updateTask($data)
    {
        $response = $this->client('post', 'robot/updatetask', $data);

        return $response;
    }

    /**********组织架构相关接口**********/

    /**
     * 创建部门.
     */
    public function createDept($data)
    {
        $response = $this->client('post', 'org/dept/create', $data);

        return $response;
    }

    /**
     * 编辑部门.
     */
    public function editDept($data)
    {
        $response = $this->client('post', 'org/dept/edit', $data);

        return $response;
    }

    /**
     * 获取架构信息.
     */
    public function structure()
    {
        $response = $this->client('get', 'org/struct/info');

        return $response;
    }

    /**
     * 创建员工.
     */
    public function createUser($data)
    {
        $response = $this->client('post', 'org/user/create', $data);

        return $response;
    }

    /**
     * 启用/禁用员工.
     */
    public function User($data)
    {
        $response = $this->client('post', 'org/user/onoff', $data);

        return $response;
    }

    /**********记录相关接口**********/

    /**
     * 电话外呼.
     */
    public function call($data)
    {
        $response = $this->client('post', 'record/call', $data);

        return $response;
    }

    /**
     * 电话空闲用户.
     */
    public function getFreeStatusUid()
    {
        $data = [
            'userState'  => 1,
        ];
        $response = $this->client('post', 'record/getFreeStatusUid', $data);

        return $response;
    }

    /**
     * 短信记录.
     */
    public function smsRecord($data)
    {
        $response = $this->client('post', 'record/smsRecord', $data);

        return $response;
    }

    /**
     * 电话记录.
     */
    public function telRecord($data)
    {
        $response = $this->client('post', 'record/telRecord', $data);

        return $response;
    }

    /**********配置相关接口**********/

    /**
     * 获取自定义字段.
     */
    public function getFieldMapping()
    {
        $response = $this->client('get', 'config/getFieldMapping');

        return $response;
    }

    /**
     * 获取标签信息.
     */
    public function getLabelInfo()
    {
        $response = $this->client('get', 'config/getLabelInfo');

        return $response;
    }

    /**
     * 获取业务组信息.
     */
    public function getPubicPond()
    {
        $response = $this->client('get', 'config/getPubicPond');

        return $response;
    }

    /**********统计相关接口**********/

    /**
     * 电话-数字图接口.
     */
    public function phoneDigitalMap($data)
    {
        $response = $this->client('post', 'statistics/digitalMap/phone', $data);

        return $response;
    }

    /**
     * 电话-折线图接口.
     */
    public function phoneLineGraph($data)
    {
        $response = $this->client('post', 'statistics/lineGraph/phone', $data);

        return $response;
    }

    /**
     * 工作效率-数字图接口.
     */
    public function workefficDigitalMap($data)
    {
        $response = $this->client('post', 'statistics/digitalMap/workeffic', $data);

        return $response;
    }

    /**
     * 工作效率-柱状图接口.
     */
    public function workfficHistogram($data)
    {
        $response = $this->client('post', 'statistics/histogram/workeffic', $data);

        return $response;
    }

    /**
     * 标签-数字图接口.
     */
    public function tagDigitalMap($data)
    {
        $response = $this->client('post', 'statistics/digitalMap/tag', $data);

        return $response;
    }

    /**
     * 标签-柱状图接口.
     */
    public function tagHistogram($data)
    {
        $response = $this->client('post', 'statistics/histogram/tag', $data);

        return $response;
    }

    /**
     * 客户数量-数字图接口.
     */
    public function crmDigitalMap($data)
    {
        $response = $this->client('post', 'statistics/digitalMap/crmQuantity', $data);

        return $response;
    }

}
