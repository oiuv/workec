<?php
/**
 * EC开放平台API.
 *
 * @author oiuv <i@oiuv.cn>
 *
 * @version 2.0.1
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
     *  签名算法.
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
     * 修改客户标签（支持批量）.
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
     *  查询客户分组(请求协议错误！???).
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
    public function queryCustomer($mobile = null, $email = null, $step = null, $labelIds = null, $followUserId = null, $publicPondId = null, $modifyTime = null, $contactTime = null, $createTime = null, $sortField = null, $sortType = null, $pageSize = 200, $pageNo = 1)
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
    public function combineCustomers($optUserId, array $detail)
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

    /**********机器人相关接口**********/

    /**********组织架构相关接口**********/

    /**
     *  获取部门和员工信息.
     */
    public function structure()
    {
        $response = $this->client('get', 'org/struct/info');

        return $response;
    }

    /**********记录相关接口**********/

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

    /**********配置相关接口**********/

    /**
     * 获取标签信息.
     */
    public function getLabelInfo()
    {
        $response = $this->client('get', 'config/getLabelInfo');

        return $response;
    }

    /**********统计相关接口**********/
}
