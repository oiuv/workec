<?php
/**
 * EC开放平台API.
 *
 * @author oiuv <i@oiuv.cn>
 *
 * @version 2.1.0
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
    private function getSign($timeStamp, $appId, $appSecret)
    {
        $sign = "appId={$appId}&appSecret={$appSecret}&timeStamp={$timeStamp}";

        return strtoupper(md5($sign));
    }

    /**********客户修改相关接口**********/

    /**
     * 创建客户.
     *
     * @param int    $optUserId    操作人ID
     * @param string $name         客户姓名，长度50个字以内
     * @param string $mobile       手机号码，默认空字符串
     * @param int    $followUserId 跟进人ID，即员工的userId, 填了跟进人则客户数据在跟进人的对应分组里查看，不填跟进人请在公共库里查看。
     * @param int    $channelId    来源ID，可使用getChannelSource()方法获取来源信息，默认为null
     * @param string $memo         备注，默认空字符串
     * @param array  $detail       要新增的客户详细数据，详细请参考CustomerDetail字段描述（https://open.workec.com/newdoc/doc/1jx5GQqVE）
     * @param bool   $notify       新增客户成功后，是否发送消息提醒用户，默认true（发送）
     * @param bool   $repeat       设置为ture并且企业管理后台设置允许创建重复客户, 那么API允许您创建重复客户，默认false（不允许）
     */
    public function addCustomer($optUserId, $name, $mobile = '', $followUserId = null, $channelId = null, $memo = '', array $detail = [], $notify = true, $repeat = false)
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
            'notify'    => $notify,
            'repeat'    => $repeat,
        ];
        $data['list'][0] += $detail;

        $response = $this->client('post', 'customer/addCustomer', $data);

        return $response;
    }

    /**
     * 批量创建客户.
     *
     * @param int   $optUserId 操作人ID
     * @param array $list      要新增的客户详细数据（不超过50条），详细请参考CustomerDetail字段描述（https://open.workec.com/newdoc/doc/1jx5GQqVE）
     * @param bool  $notify    新增客户成功后，是否发送消息提醒用户，默认true（发送）
     * @param bool  $repeat    设置为ture并且企业管理后台设置允许创建重复客户, 那么API允许您创建重复客户，默认false（不允许）
     */
    public function addCustomers($optUserId, array $list, $notify = true, $repeat = false)
    {
        $data = [
            'optUserId' => $optUserId,
            'list'      => $list,
            'notify'    => $notify,
            'repeat'    => $repeat,
        ];
        $response = $this->client('post', 'customer/addCustomer', $data);

        return $response;
    }

    /**
     * 修改客户信息.
     *
     * @param int   $optUserId 操作人ID
     * @param int   $crmId     要修改的客户ID
     * @param array $detail    要修改的客户数据，详细请参考修改客户信息入参说明（https://open.workec.com/newdoc/doc/1jxcAT6VE）
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
     *
     * @param array $data 需要批量修改的客户信息数组，请参考接口SaveCrmDetailVo对象信息（https://open.workec.com/newdoc/doc/1jxW4Oaia）
     */
    public function batchUpdateCustomer(array $data)
    {
        $response = $this->client('post', 'customer/batchUpdateCustomer', $data);

        return $response;
    }

    /**
     * 合并客户.
     *
     * @param int    $optUserId    操作人ID
     * @param int    $followUserId 跟进人ID，即员工的userId
     * @param string $name         客户姓名，长度50个字以内
     * @param string $crmIds       需要合并的多个客户的id，以逗号隔开，合并后，选取最小客户id 做为新的客户id
     * @param array  $detail       要合并的客户数据，详细请参考接口入参说明（https://open.workec.com/newdoc/doc/1jxPqcTZ6）
     */
    public function combineCustomer($optUserId, $followUserId, $name, $crmIds, array $detail)
    {
        $data = [
            'optUserId'    => $optUserId,
            'followUserId' => $followUserId,
            'name'         => $name,
            'crmIds'       => $crmIds,
        ];
        $data += $detail;

        $response = $this->client('post', 'customer/combine', $data);

        return $response;
    }

    /**
     * todo 客户资料-文件上传(支持批量).
     */

    /**
     * 共享客户.
     *
     * @param int   $optUserId 操作人ID
     * @param array $crmIds    客户ID列表，单次最多输入200个id
     * @param array $userIds   员工id列表，单次最多可以输入50个id
     * @param int   $type      共享方式（默认值为1）：1. 保持原有共享关系，新增共享同事 2. 用新的共享同事覆盖原有共享关系
     */
    public function shareCustomer($optUserId, array $crmIds, array $userIds, $type = 1)
    {
        $data = [
            'optUserid' => $optUserId,
            'crmIds'    => $crmIds,
            'userIds'   => $userIds,
            'type'      => $type,
        ];
        $response = $this->client('post', 'customer/share', $data);

        return $response;
    }

    /**
     * 放弃客户(支持批量).
     *
     * @param int    $optUserId 操作人ID
     * @param string $crmIds    需要放弃的的多个客户的id列表，以英文逗号分隔，最多200个
     * @param int    $type      放弃方式（默认值为1）：type=0 放弃到业务组公海，type>0 放弃到公司大公海
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
     *
     * @param int    $optUserId    操作人ID
     * @param string $crmIds       需要领取或转让的多个客户的id列表，以英文逗号分隔，最多200个
     * @param int    $followUserId 跟进人ID，即员工的userId
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
     * 修改客户阶段(支持批量).
     *
     * @param int    $optUserId 操作人ID
     * @param string $crmIds    需要统一修改阶段的多个客户的id列表，以英文逗号分隔
     * @param int    $step      客户阶段ID，可以使用方法getStages()获取客户进展(客户阶段)ID
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

    /**********客户查询相关接口**********/

    /**
     * 获取客户头像.
     *
     * @param int   $pageNo    页码，默认值为1
     * @param int   $pageSize  每页的数量，默认值为20
     * @param array $crmIdList 客户的ID，如果填写了，分页的参数就不再生效(最大值200)
     */
    public function customerFace($pageNo = 1, $pageSize = 20, array $crmIdList = [])
    {
        $data = [
            'crmIdList' => $crmIdList,
            'pageSize'  => $pageSize,
            'pageNo'    => $pageNo,
        ];
        $response = $this->client('post', 'customer/face', $data);

        return $response;
    }

    /**
     * 查询客户标签(支持批量).
     *
     * @param string $crmIds 客户id列表，多个id使用英文逗号分隔（不超过200）
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
     * 查询客户列表(相比较分页查询客户列表，这个接口功能更加强大，查询条件更加丰富).
     *
     * @param array $data 需要查询的条件组合，请参考接口的入参说明（https://open.workec.com/newdoc/doc/257slYQKO）
     */
    public function queryList(array $data)
    {
        $response = $this->client('post', 'customer/queryList', $data);

        return $response;
    }

    /**
     * 客户资料-文件列表查询.
     *
     * @param array $crmIds   查询哪些客户的文件，这里指定了客户id列表，列表长度不能超过10个客户
     * @param int   $folderId 指定要查询的目录id，根目录Id约定为0，若该参数为空，将返回客户的所有文件
     */
    public function fileList(array $crmIds, $folderId = null)
    {
        $data = [
            'crmIds'   => $crmIds,
            'folderId' => $folderId,
        ];
        $response = $this->client('post', 'customer/file/list', $data);

        return $response;
    }

    /**
     * 客户资料-文件目录查询.
     *
     * @param array $crmIds 客户id列表，指定查询哪些客户的目录信息，列表长度不能超过10个客户，如果不传递，那么只查询公共的目录信息
     */
    public function folderList(array $crmIds = null)
    {
        $data = [
            'crmIds' => $crmIds,
        ];
        $response = $this->client('post', 'customer/folder/list', $data);

        return $response;
    }

    /**
     * 手机查询客户(判重).
     *
     * @param string $mobile           手机号码，多个号码英文逗号分开，最多50个，系统手机号码或者自定义手机号码
     * @param string $maxNumsPreMobile 每个号码最多返回多少个 CustomerInfoItem 明细数据，如果只关心是否重复建议设置为0，如重复数据不多，可根据需求设置，建议小于100，如不设置默认值为1
     * @param array  $includes         需要返回的字段，如果不指定只返回crmId和fieldId的值，其它字段返回null，具体参考接口的CustomerInfoItem字段（https://open.workec.com/newdoc/doc/1Ox1UVJkkM）
     */
    public function queryExist($mobile, $maxNumsPreMobile = 1, array $includes = [])
    {
        $data = [
            'mobile'           => $mobile,
            'maxNumsPreMobile' => $maxNumsPreMobile,
            'includes'         => $includes,
        ];
        $response = $this->client('post', 'customer/queryExist', $data);

        return $response;
    }

    /**
     * 分页查询客户信息 (建议使用 查询客户列表 接口代替).
     *
     * @param int    $mobile       手机号码，非必填
     * @param string $email        邮箱，非必填
     * @param int    $step         客户阶段，非必填
     * @param string $labelIds     标签ID列表，非必填，使用英文逗号分隔，可使用getLabelInfo()方法获取标签ID列表
     * @param int    $followUserId 跟进人ID，非必填
     * @param int    $publicPondId 业务组ID，非必填，可使用getPubicPond()方法获取业务组ID
     * @param array  $modifyTime   动态时间，非必填，参数格式示例：["startTime"=>"2020-08-25 00:00:00","endTime"=>"2020-08-25 10:00:00"]
     * @param array  $contactTime  联系时间，非必填，参数格式示例：["startTime"=>"2020-08-25 00:00:00","endTime"=>"2020-08-25 10:00:00"]
     * @param array  $createTime   创建时间，非必填，参数格式示例：["startTime"=>"2020-08-25 00:00:00","endTime"=>"2020-08-25 10:00:00"]
     * @param array  $orderBy      排序，非必填，参数sortField只支持三个值: createtime、modifytime、contacttime，格式示例：["sortField"=>"contacttime","sortType"=>"asc"]
     * @param array  $pageInfo     分页，非必填，参数格式示例：["pageNo"=>1,"pageSize"=>200]
     */
    public function getCustomer($mobile = null, $email = null, $step = null, $labelIds = null, $followUserId = null, $publicPondId = null, array $modifyTime = null, array $contactTime = null, array $createTime = null, array $orderBy = null, array $pageInfo = null)
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
            'orderBy'      => $orderBy,
            'pageInfo'     => $pageInfo,
        ];
        $response = $this->client('post', 'customer/query', $data);

        return $response;
    }

    /**
     * 批量查询客户信息(建议使用 查询客户列表 接口代替).
     *
     * @param string $crmIds 客户id列表，多个id使用英文逗号分隔
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
     * 导出客户.
     *
     * @param array $sort    根据orderBy条件查询的结果数组，数组元素个数需和排序条件个数一致，初始值可填0，将查询结果返回的sort做为参数传入实现翻页
     * @param array $orderBy 查询排序方式数组，sortFiled可以为modifyTime、contactTime、createTime、crmId、followUserId和channel，sortType可以为asc和desc
     * @param array $detail  可选查询条件字段，具体参考接口参数说明（https://open.workec.com/newdoc/doc/zDwrbi9Xc）
     */
    public function queryCustomer(array $sort = [0, 0], array $orderBy = [['sortField'=>'crmId', 'sortType'=>'asc'], ['sortField'=>'contactTime', 'sortType'=>'desc']], array $detail = [])
    {
        $data = [
            'sort'    => $sort,
            'orderBy' => $orderBy,
        ];
        $data += $detail;
        $response = $this->client('post', 'customer/queryCustomer', $data);

        return $response;
    }

    /**
     * 查询客户分组.
     *
     * @param int $userId 用户id(员工id)
     */
    public function getCustomerGroup($userId)
    {
        $data = "?userId=$userId";
        $response = $this->client('GET', 'customer/getCustomerGroup'.$data);

        return $response;
    }

    /**
     * 客户 - 获取删除的客户.
     *
     * @param string $startTime 查询删除客户的开始时间，格式yyyy-MM-dd HH:mm:ss
     * @param string $endTime   查询删除客户的截止时间，格式yyyy-MM-dd HH:mm:ss，与startTime最大间隔7天
     * @param string $lastId    根据此参数来进行翻页，上一次请求得到的最后一条记录中的id，初始值可为""
     */
    public function delcrms($startTime = '', $endTime = '', $lastId = '')
    {
        $data = [
            'startTime' => $startTime,
            'endTime'   => $endTime,
            'lastId'    => $lastId,
        ];
        $response = $this->client('post', 'customer/delcrms', $data);

        return $response;
    }

    /**********统一查询接口**********/

    /**
     * 查询指定id的销售计划.
     *
     * @param $params 业务id对应的参数
     */
    public function querySaleTask(int ...$params)
    {
        $data = [
            'serviceId' => 'querySaleTask',
            'params'    => $params,
        ];
        $response = $this->client('post', 'special/select', $data);

        return $response;
    }

    /**
     * 查询客户进展统计（分析客户转化）.
     *
     * @param string $startTime 开始时间，格式：yyyy-MM-dd HH:mm:ss
     * @param string $endTime   结束时间，格式：yyyy-MM-dd HH:mm:ss
     */
    public function queryStepCount($startTime, $endTime)
    {
        $data = [
            'serviceId' => 'queryStepCount',
            'params'    => [$startTime, $endTime],
        ];
        $response = $this->client('post', 'special/select', $data);

        return $response;
    }

    /**
     * 查询创建订单数和成交订单数.
     *
     * @param string $startTime 开始日期，格式：yyyy-MM-dd
     * @param string $endTime   结束日期，格式：yyyy-MM-dd
     */
    public function queryOrderCount($startTime, $endTime)
    {
        $data = [
            'serviceId' => 'queryOrderCount',
            'params'    => [$startTime, $endTime],
        ];
        $response = $this->client('post', 'special/select', $data);

        return $response;
    }

    /**********沟通记录&轨迹接口**********/

    /**
     * 分页查询客户轨迹（跟进记录也能通过这个接口查询）.
     *
     * @param string $startTime      必填，轨迹查询的开始时间，格式：yyyy-MM-dd HH:mm:ss
     * @param string $endTime        必填，轨迹查询的结束时间，不能大于 startTime 31 天，格式：yyyy-MM-dd HH:mm:ss
     * @param int    $trajectoryType 轨迹类型，如果不填写，则查询所有类型的轨迹，具体轨迹类型可查文档（https://open.workec.com/newdoc/doc/1jPZyYjTY）
     * @param string $crmIds         如果填写了，只会查询填写范围内的客户轨迹，英文逗号分隔
     * @param int    $lastId         分页查询用到，本次查询范围的 trajectoryId > lastId,默认为0
     * @param string $lastTime       分页查询用到，如果填写了，并且 startTime <= lastTime, 那么本次查询轨迹的时间范围在 lastTime 和 endTime 之间
     * @param int    $pageSize       分页大小，默认200，取值在 [1,200] 之间
     */
    public function getTrajectory($startTime, $endTime, $trajectoryType = null, $crmIds = '', $lastId = 0, $lastTime = '', $pageSize = 200)
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
     * 电话外呼.
     *
     * @param int    $userid     用户ID(1-10位)
     * @param string $phone      呼叫的号码，最多16个字符
     * @param int    $clientType 呼叫终端类型, 默认是0，0 : EC PC客户端外呼, 1: EC 手机APP外呼, 3 使用云呼拨打，其他类型不支持
     * @param array  $detail     被呼叫用户的其它参数，具体见接口入参文档（https://open.workec.com/newdoc/doc/26Ycn7Sj6）
     */
    public function call($userid, $phone, $clientType = 0, array $detail = [])
    {
        $data = [
            'userid'     => $userid,
            'phone'      => $phone,
            'clientType' => $clientType,
        ];
        $data += $detail;
        $response = $this->client('post', 'record/call', $data);

        return $response;
    }

    /**
     * 电话空闲用户.
     *
     * @param int $userState 员工状态(1-在线 2-在忙)
     */
    public function getFreeStatusUid($userState = 1)
    {
        $data = [
            'userState' => $userState,
        ];
        $response = $this->client('post', 'record/getFreeStatusUid', $data);

        return $response;
    }

    /**
     * 短信记录.
     *
     * @param string $startTime 必填，查询的开始时间，必须是最近31天以内的时间，格式：yyyy-MM-dd HH:mm:ss
     * @param string $endTime   必填，查询的结束时间，不能大于 startTime 31天，格式：yyyy-MM-dd HH:mm:ss
     * @param array  $detail    其它可选参数，具体见接口入参说明（https://open.workec.com/newdoc/doc/26ZfNKxJk）
     */
    public function smsRecord($startTime, $endTime, array $detail = [])
    {
        $data = [
            'date' => [
                'endTime'   => $endTime,
                'startTime' => $startTime,
            ],
        ];
        $data += $detail;
        $response = $this->client('post', 'record/smsRecord', $data);

        return $response;
    }

    /**
     * 电话记录.
     *
     * @param string $startTime 必填，查询的开始时间，必须是最近31天以内的时间，格式：yyyy-MM-dd HH:mm:ss
     * @param string $endTime   必填，查询的结束时间，不能大于 startTime 31天，格式：yyyy-MM-dd HH:mm:ss
     * @param array  $detail    其它可选参数，具体见接口入参说明（https://open.workec.com/newdoc/doc/26a467lLQ）
     */
    public function telRecord($startTime, $endTime, array $detail = [])
    {
        $data = [
            'date' => [
                'endTime'   => $endTime,
                'startTime' => $startTime,
            ],
        ];
        $data += $detail;
        $response = $this->client('post', 'record/telRecord', $data);

        return $response;
    }

    /**
     * 联系记录 - 导出跟进记录(建议使用【客户轨迹】接口替换此接口进行查询).
     *
     * @param string $startDate 统计开始时间，精确到年月日，如：2016-03-10（不能查询当天的数据,有需要可使用查询客户轨迹的接口）
     * @param string $endDate   统计结束时间，精确到年月日，如：2016-03-10（不能查询当天的数据,有需要可使用查询客户轨迹的接口）
     * @param string $userIds   员工ID，希望导出的操作员工ID，可输入多个值，用分号(;)隔开(最大值50)，可选参数，默认查询所有员工
     * @param string $crmIds    客户ID，被呼叫的客户的ID，可输入多个值，用分号(;)隔开(最大值200)，可选参数，默认查询所有客户
     * @param int    $pageNo    分页请求时当前请求第几页的数据(每页50条)，可选参数，默认值为1
     */
    public function findUserTrajectory($startDate, $endDate, $userIds = '', $crmIds = '', $pageNo = 1)
    {
        $data = [
            'endDate'   => $endDate,
            'startDate' => $startDate,
            'userIds'   => $userIds,
            'crmIds'    => $crmIds,
            'pageNo'    => $pageNo,
        ];
        $response = $this->client('post', 'trajectory/findUserTrajectory', $data);

        return $response;
    }

    /**
     * 联系记录 - 批量添加跟进记录.
     *
     * @param array $data 跟进记录列表数组（不超过200），具体参数见接口文档（https://open.workec.com/newdoc/doc/TEKMFExzK）
     */
    public function saveUserTrajectory(array $data)
    {
        $response = $this->client('post', 'trajectory/saveUserTrajectory', $data);

        return $response;
    }

    /**
     * 联系记录 - 导出历史跟进记录.
     *
     * @param int    $year            统计时间年，如：2017
     * @param int    $month           统计时间月，如：1 取值[1-12]
     * @param int    $startDayOfMonth 统计时间开始日，取值[1-31] 不填默认取1
     * @param int    $endDayOfMonth   统统计时间结束日，取值[1-31] 不填默认取至当月最后一天
     * @param int    $pageNo          分页请求时当前请求第几页的数据(每页50条)。不填默认值为1
     * @param string $userIds         员工ID，希望导出的操作员工ID，可输入多个值，用分号(;)隔开(最大值50)，可选参数，默认查询所有员工
     * @param string $crmIds          客户ID，被呼叫的客户的ID，可输入多个值，用分号(;)隔开(最大值200)，可选参数，默认查询所有客户
     */
    public function findHistoryUserTrajectory($year, $month, $startDayOfMonth = 1, $endDayOfMonth = 31, $pageNo = 1, $userIds = '', $crmIds = '')
    {
        $data = [
            'year'            => $year,
            'month'           => $month,
            'startDayOfMonth' => $startDayOfMonth,
            'endDayOfMonth'   => $endDayOfMonth,
            'userIds'         => $userIds,
            'crmIds'          => $crmIds,
            'pageNo'          => $pageNo,
        ];
        $response = $this->client('post', 'trajectory/findHistoryUserTrajectory', $data);

        return $response;
    }

    /**
     * 联系记录 - 导出历史短信记录.
     *
     * @param int    $year            统计时间年，如：2017
     * @param int    $month           统计时间月，如：1 取值[1-12]
     * @param int    $startDayOfMonth 统计时间开始日，取值[1-31] 不填默认取1
     * @param int    $endDayOfMonth   统统计时间结束日，取值[1-31] 不填默认取至当月最后一天
     * @param int    $pageNo          分页请求时当前请求第几页的数据(每页50条)。不填默认值为1
     * @param string $userIds         员工ID，希望导出的操作员工ID，可输入多个值，用分号(;)隔开(最大值50)，可选参数，默认查询所有员工
     * @param string $crmIds          客户ID，被呼叫的客户的ID，可输入多个值，用分号(;)隔开(最大值200)，可选参数，默认查询所有客户
     */
    public function sendSmsHistory($year, $month, $startDayOfMonth = null, $endDayOfMonth = null, $pageNo = 1, $userIds = '', $crmIds = '')
    {
        $data = [
            'year'            => $year,
            'month'           => $month,
            'startDayOfMonth' => $startDayOfMonth,
            'endDayOfMonth'   => $endDayOfMonth,
            'userIds'         => $userIds,
            'crmIds'          => $crmIds,
            'pageNo'          => $pageNo,
        ];
        $response = $this->client('post', 'record/sendSmsHistory', $data);

        return $response;
    }

    /**
     * 联系记录 - 导出电话历史记录.
     *
     * @param string $startTime        查询起始时间，格式：yyyy-MM-dd HH:mm:ss，开始时间不能大于结束时间，不能超过当前时间
     * @param string $endTime          查询结束时间，格式：yyyy-MM-dd HH:mm:ss，接口单次请求时间区间范围最大为31天
     * @param int    $preLastWasteId   上一次查询页的最后一条数据的wasteId，可选
     * @param string $preLastStartTime 上一次查询页的最后一条数据的开始时间，可选
     * @param int    $pageSize         每页显示数据量（默认200条），可选
     * @param string $phoneNo          被呼叫的电话号码，可选
     * @param string $userIds          员工ID，希望导出的操作员工ID，可输入多个值，用分号(;)隔开(最大值50)，可选参数，默认查询所有员工
     * @param string $crmIds           客户ID，被呼叫的客户的ID，可输入多个值，用分号(;)隔开(最大值200)，可选参数，默认查询所有客户
     */
    public function telRecordHistoryQuery($startTime, $endTime, $preLastWasteId = null, $preLastStartTime = null, $pageSize = 200, $phoneNo = '', $userIds = '', $crmIds = '')
    {
        $data = [
            'date' => [
                'startTime' => $startTime,
                'endTime'   => $endTime,
            ],
            'page' => [
                'pageSize'         => $pageSize,
                'preLastWasteId'   => $preLastWasteId,
                'preLastStartTime' => $preLastStartTime,

            ],
            'userIds' => $userIds,
            'crmIds'  => $crmIds,
            'phoneNo' => $phoneNo,
        ];
        $response = $this->client('post', 'record/telRecordHistoryQuery', $data);

        return $response;
    }

    /**
     * 批量添加电话记录.
     *
     * @param array $data 电话记录信息集合，具体参数见接口文档（https://open.workec.com/newdoc/doc/tvpLVa1Ib）
     */
    public function addTelRecord(array $data)
    {
        $response = $this->client('post', 'record/addTelRecord', $data);

        return $response;
    }

    /**********组织架构相关接口**********/

    /**
     * 客户 - 获取员工签到记录.
     *
     * @param array $userIds 员工ID数组,通过组织架构接口取得员工和部门信息。
     * @param array $detail  其它可选参数，具体见接口文档（https://open.workec.com/newdoc/doc/1Rb4uSYM7）
     */
    public function getCrmVisitDetails(array $userIds, array $detail = [])
    {
        $data = [
            'userIds' => $userIds,
        ];
        $data += $detail;
        $response = $this->client('post', 'customer/getCrmVisitDetails', $data);

        return $response;
    }

    /**
     * 组织架构 - 获取指定员工信息.
     *
     * @param string $account  用户账号(登录账号，不一定是手机号码)。userId、account 必须填写一个。
     * @param int    $userId   用户ID。 userId、account 必须填写一个，另一个可为null。
     * @param bool   $deptInfo 是否返回部门id和部门名称, 默认 false ,不返回
     */
    public function findUserInfoById($account, $userId = null, $deptInfo = false)
    {
        $data = [
            'account'  => $account,
            'userId'   => $userId,
            'deptInfo' => $deptInfo,
        ];
        $response = $this->client('post', 'org/user/findUserInfoById', $data);

        return $response;
    }

    /**
     * 创建部门.
     *
     * @param int    $optUserId    操作人ID
     * @param string $deptName     部门名称
     * @param int    $parentDeptId 父部门ID，默认：0(根部门)
     */
    public function createDept($optUserId, $deptName, $parentDeptId = 0)
    {
        $data = [
            'optUserId'    => $optUserId,
            'deptName'     => $deptName,
            'parentDeptId' => $parentDeptId,
        ];
        $response = $this->client('post', 'org/dept/create', $data);

        return $response;
    }

    /**
     * 编辑部门.
     *
     * @param int    $optUserId    操作人ID
     * @param int    $deptId       部门ID
     * @param string $deptName     部门名称，默认：null(不修改)
     * @param int    $parentDeptId 父部门ID，默认：0(根部门)
     */
    public function editDept($optUserId, $deptId, $deptName = null, $parentDeptId = 0)
    {
        $data = [
            'optUserId'    => $optUserId,
            'deptId'       => $deptId,
            'deptName'     => $deptName,
            'parentDeptId' => $parentDeptId,
        ];
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
     * 启用/禁用员工.
     *
     * @param int $optUserId 操作人ID
     * @param int $userId    员工ID
     * @param int $status    状态 0：禁用，1：启用
     */
    public function User($optUserId, $userId, $status)
    {
        $data = [
            'optUserId' => $optUserId,
            'userId'    => $userId,
            'status'    => $status,
        ];
        $response = $this->client('post', 'org/user/onoff', $data);

        return $response;
    }

    /**
     * 创建员工.
     *
     * @param int    $optUserId 操作人ID
     * @param string $name      员工姓名，长度在2-20字之间
     * @param string $account   手机账号
     * @param string $title     职位，长度最多20字
     * @param string $email     邮箱（可选），长度80个字以内，必须包含“@”和“.”
     * @param int    $deptId    部门ID（可选）
     */
    public function createUser($optUserId, $name, $account, $title, $email = null, $deptId = null)
    {
        $data = [
            'optUserId' => $optUserId,
            'name'      => $name,
            'account'   => $account,
            'title'     => $title,
            'deptId	'   => $deptId,
            'email'     => $email,
        ];
        $response = $this->client('post', 'org/user/create', $data);

        return $response;
    }

    /**
     * 组织架构 - 修改员工.
     *
     * @param int   $optUserId 操作人ID
     * @param int   $userId    员工ID
     * @param array $detail    需要修改的员工资料
     */
    public function updateUser($optUserId, $userId, $detail)
    {
        $data = [
            'optUserId' => $optUserId,
            'userId'    => $userId,
        ];
        $data += $detail;
        $response = $this->client('post', 'org/user/updateUser', $data);

        return $response;
    }

    /**********机器人相关接口**********/

    /**
     * 新增一条电话机器人任务.
     *
     * @param string $title      任务标题
     * @param int    $type       任务状态
     * @param int    $userId     创建人ID
     * @param string $time       创建时间
     * @param string $finishTime 完成时间
     * @param string $finish     任务拨打量
     * @param string $total      任务总量
     * @param string $craft      电话话术
     * @param int    $robotId    机器人厂家ID
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
     * 新增机器人通话记录.
     *
     * @param array $data 输入参数请参考接口文档（https://open.workec.com/newdoc/doc/W3ttrm0sy）
     */
    public function addTaskRecord(array $data)
    {
        $response = $this->client('post', 'robot/addtaskrecord', $data);

        return $response;
    }

    /**
     * 更新电话机器人任务.
     *
     * @param int    $taskId     任务ID
     * @param string $finishTime 完成时间
     * @param array  $detail     任务其它可选参数，具体参考接口文档（https://open.workec.com/newdoc/doc/W3uOjKgNx）
     */
    public function updateTask($taskId, $finishTime, array $detail = [])
    {
        $data = [
            'taskId'     => $taskId,
            'finishTime' => $finishTime,
        ];
        $data += $detail;
        $response = $this->client('post', 'robot/updatetask', $data);

        return $response;
    }

    /**********异步任务相关接口**********/

    /**
     * 创建异步任务.
     *
     * @param string $taskName 异步任务名称
     * @param int    $type     任务类型，异步导出客户任务type为1，异步导出轨迹任务type为2，异步导出电话任务type为3，异步导出短信任务type为4
     * @param array  $detail   任务其它参数，具体参考接口文档（https://open.workec.com/newdoc/doc/25dCCP4fw）
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
     * 查询当前异步任务的状态.
     *
     * @param int $taskId     异步任务ID
     * @param int $taskStatus 异步任务状态
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

    /**********配置相关接口**********/

    /**
     * 获取 企业联系人 自定义字段.
     */
    public function getBookFieldMapping()
    {
        $response = $this->client('get', 'config/getBookFieldMapping');

        return $response;
    }

    /**
     * 获取自定义字段.
     */
    public function getFieldMapping()
    {
        $response = $this->client('get', 'config/getFieldMapping');

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

    /**
     * 获取客户来源信息.
     */
    public function getChannelSource()
    {
        $response = $this->client('get', 'customer/getChannelSource');

        return $response;
    }

    /**
     * 获取客户进展(阶段)信息.
     */
    public function getStages()
    {
        $response = $this->client('get', 'config/getStages');

        return $response;
    }

    /**
     * 客户 - 获取级联字段信息.
     *
     * @param array $fieldIds 级联字段ID；通过企业管理后台查看，或通过【获取自定义字段信息】接口获取（fieldType=0，为级联字段）
     * @param int   $lastId   根据每页返回的最后一条数据的paramId进行翻页，首次查询可不传该值
     */
    public function getCasCadeFieldMapping(array $fieldIds, $lastId = null)
    {
        $data = [
            'fieldIds' => $fieldIds,
            'lastId'   => $lastId,
        ];
        $response = $this->client('post', 'customer/getCasCadeFieldMapping', $data);

        return $response;
    }

    /**********统计相关接口**********/

    /**
     * 电话-数字图接口.
     *
     * @param int   $corpId         企业ID
     * @param int   $userId         用户ID
     * @param array $businessIndexs 业务指标 【11:通话时长,12:拨打次数,13:接通次数,14:接通率,15:平均通话时长,16:联系人数量】
     * @param array $apiParams      电话数字图参数，参考接口文档apiParams参数说明（https://open.workec.com/newdoc/doc/26puFnngC）
     * @param int   $sequential     是否包括环比数据，1 有环比， 0 无环比
     */
    public function phoneDigitalMap($corpId, $userId, array $businessIndexs, array $apiParams, $sequential = 1)
    {
        $data = [
            'corpId'         => $corpId,
            'userId'         => $userId,
            'businessIndexs' => $businessIndexs,
            'apiParams'      => $apiParams,
            'sequential'     => $sequential,
        ];
        $response = $this->client('post', 'statistics/digitalMap/phone', $data);

        return $response;
    }

    /**
     * 电话-折线图接口.
     *
     * @param int   $corpId         企业ID
     * @param int   $userId         用户ID
     * @param array $businessIndexs 业务指标 【11:通话时长,12:拨打次数,13:接通次数,14:接通率,15:平均通话时长,16:联系人数量】
     * @param array $apiParams      电话拆线图参数，参考接口文档apiParams参数说明（https://open.workec.com/newdoc/doc/26qcIjU40）
     */
    public function phoneLineGraph($corpId, $userId, array $businessIndexs, array $apiParams)
    {
        $data = [
            'corpId'         => $corpId,
            'userId'         => $userId,
            'businessIndexs' => $businessIndexs,
            'apiParams'      => $apiParams,
        ];
        $response = $this->client('post', 'statistics/lineGraph/phone', $data);

        return $response;
    }

    /**
     * 工作效率-数字图接口.
     *
     * @param int   $corpId         企业ID
     * @param int   $userId         用户ID
     * @param array $businessIndexs 业务指标，参考接口文档中支持的统计指标（https://open.workec.com/newdoc/doc/26qxRIqAK）
     * @param array $apiParams      工作效率数字图参数，参考接口文档apiParams参数说明（https://open.workec.com/newdoc/doc/26qxRIqAK）
     * @param int   $sequential     是否包括环比数据，1 有环比， 0 无环比
     */
    public function workefficDigitalMap($corpId, $userId, array $businessIndexs, array $apiParams, $sequential = 1)
    {
        $data = [
            'corpId'         => $corpId,
            'userId'         => $userId,
            'businessIndexs' => $businessIndexs,
            'apiParams'      => $apiParams,
            'sequential'     => $sequential,
        ];
        $response = $this->client('post', 'statistics/digitalMap/workeffic', $data);

        return $response;
    }

    /**
     * 工作效率-柱状图接口.
     *
     * @param int   $corpId         企业ID
     * @param int   $userId         用户ID
     * @param array $businessIndexs 业务指标，参考接口文档中支持的统计指标（https://open.workec.com/newdoc/doc/26rAbuOJs）
     * @param array $apiParams      工作效率柱状图参数，参考接口文档apiParams参数说明（https://open.workec.com/newdoc/doc/26rAbuOJs）
     */
    public function workfficHistogram($corpId, $userId, array $businessIndexs, array $apiParams)
    {
        $data = [
            'corpId'         => $corpId,
            'userId'         => $userId,
            'businessIndexs' => $businessIndexs,
            'apiParams'      => $apiParams,
        ];
        $response = $this->client('post', 'statistics/histogram/workeffic', $data);

        return $response;
    }

    /**
     * 标签-数字图接口.
     *
     * @param int   $corpId         企业ID
     * @param int   $userId         用户ID
     * @param array $businessIndexs 业务指标 【41:标签新增量,42:标签删除量,43:标签净增量】（只能填写一个指标）
     * @param array $apiParams      标签数字图参数，参考接口文档apiParams参数说明（https://open.workec.com/newdoc/doc/272UwodYC）
     * @param int   $sequential     是否包括环比数据，1 有环比， 0 无环比
     */
    public function tagDigitalMap($corpId, $userId, array $businessIndexs, array $apiParams, $sequential = 1)
    {
        $data = [
            'corpId'         => $corpId,
            'userId'         => $userId,
            'businessIndexs' => $businessIndexs,
            'apiParams'      => $apiParams,
            'sequential'     => $sequential,
        ];
        $response = $this->client('post', 'statistics/digitalMap/tag', $data);

        return $response;
    }

    /**
     * 标签-柱状图接口.
     *
     * @param int   $corpId         企业ID
     * @param int   $userId         用户ID
     * @param array $businessIndexs 业务指标 【41:标签新增量,42:标签删除量,43:标签净增量】（只能填写一个指标）
     * @param array $apiParams      标签柱状图参数，参考接口文档apiParams参数说明（https://open.workec.com/newdoc/doc/26ryQMgEK）
     */
    public function tagHistogram($corpId, $userId, array $businessIndexs, array $apiParams)
    {
        $data = [
            'corpId'         => $corpId,
            'userId'         => $userId,
            'businessIndexs' => $businessIndexs,
            'apiParams'      => $apiParams,
        ];
        $response = $this->client('post', 'statistics/histogram/tag', $data);

        return $response;
    }

    /**
     * 客户数量-数字图接口.
     *
     * @param int   $corpId         企业ID
     * @param int   $userId         用户ID
     * @param array $businessIndexs 业务指标 【11:通话时长,12:通话次数,13:接通次数,14:接通率,15:平均通话时长,16:联系人数量】
     * @param array $apiParams      客户数量数字图参数，参考接口文档apiParams参数说明（https://open.workec.com/newdoc/doc/26t4jjnSe）
     * @param int   $sequential     是否包括环比数据，1 有环比， 0 无环比
     */
    public function crmDigitalMap($corpId, $userId, array $businessIndexs, array $apiParams, $sequential = 1)
    {
        $data = [
            'corpId'         => $corpId,
            'userId'         => $userId,
            'businessIndexs' => $businessIndexs,
            'apiParams'      => $apiParams,
            'sequential'     => $sequential,
        ];
        $response = $this->client('post', 'statistics/digitalMap/crmQuantity', $data);

        return $response;
    }

    /**
     * 按照渠道统计每个阶段的客户数.
     *
     * @param string $startDate 开始时间，格式：yyyy-MM-dd
     * @param string $endDate   结束时间，格式：yyyy-MM-dd，注意结束时间不能大于开始时间 31 天
     */
    public function queryStepCountByChannel($startDate, $endDate)
    {
        $data = [
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ];
        $response = $this->client('post', 'statistics/crmStats/queryStepCountByChannel', $data);

        return $response;
    }

    /**
     * TOP N客户渠道统计.
     *
     * @param int    $topNum      转化率前n数据
     * @param int    $startStepId 开始阶段
     * @param int    $endStepId   结束阶段
     * @param string $startDate   开始时间，格式：yyyy-MM-dd
     * @param string $endDate     结束时间，格式：yyyy-MM-dd，注意结束时间不能大于开始时间 31 天
     * @param array  $userIdList  用户id列表，可选参数
     */
    public function getTopStepCountByChannel($topNum, $startStepId, $endStepId, $startDate, $endDate, array $userIdList = [])
    {
        $data = [
            'topNum'      => $topNum,
            'startStepId' => $startStepId,
            'endStepId'   => $endStepId,
            'userIdList'  => $userIdList,
            'date'        => [
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ],
        ];
        $response = $this->client('post', 'statistics/crmStats/getTopStepCountByChannel', $data);

        return $response;
    }

    /**
     * 阶段客户数统计.
     *
     * @param string $startDate  开始时间，格式：yyyy-MM-dd
     * @param string $endDate    结束时间，格式：yyyy-MM-dd，注意结束时间不能大于开始时间 31 天
     * @param array  $userIdList 用户id列表，可选参数
     */
    public function getStepCount($startDate, $endDate, array $userIdList = [])
    {
        $data = [
            'userIdList' => $userIdList,
            'date'       => [
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ],
        ];
        $response = $this->client('post', 'statistics/crmStats/getStepCount', $data);

        return $response;
    }

    /**
     * 各用户(员工)的客户数量统计.
     *
     * @param int   $queryType 查询类型: 【1:员工 4:企业】，默认值为4
     * @param array $ids       用户ID列表，如果是queryType=4, ids 参数将被忽略
     */
    public function groupbyUserIds($queryType = 4, array $ids = [])
    {
        $data = [
            'queryType' => $queryType,
            'ids'       => $ids,
        ];
        $response = $this->client('post', 'statistics/crmStats/groupbyUserIds', $data);

        return $response;
    }

    /**********销售计划相关接口**********/
    // todo

    /**********销售订单相关接口**********/
    // todo

    /**********客户标签相关接口**********/

    /**
     * 客户标签 - 创建标签分组.
     *
     * @param int    $userId 操作人ID
     * @param string $name   标签分组名
     * @param int    $type   分组类型，默认值为0，取值： 0 代表此分组的标签可以多选 1 代表此分组的标签只能单选
     * @param string $color  分组颜色，默认值为c1，取值范围[c1~c20]
     */
    public function addLabelGroup($userId, $name, $type = 0, $color = 'c1')
    {
        $data = [
            'userId' => $userId,
            'name'   => $name,
            'color'  => $color,
            'type'   => $type,
        ];
        $response = $this->client('post', 'label/addLabelGroup', $data);

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
     * 客户标签 - 创建标签.
     *
     * @param int    $userId     操作人ID
     * @param string $name       标签名
     * @param string $groupValue 标签分组id
     */
    public function addLabel($userId, $name, $groupValue)
    {
        $data = [
            'userId'      => $userId,
            'name'        => $name,
            'groupValue'  => $groupValue,
        ];
        $response = $this->client('post', 'label/addLabel', $data);

        return $response;
    }

    /**
     * 修改客户标签(支持批量).
     *
     * @param int    $optUserId 操作员ID
     * @param string $crmIds    需要操作的客户ID列表，使用英文逗号分隔，不超过200个
     * @param string $labels    需要替换的标签ID列表，使用英文逗号分隔
     * @param int    $type      替换标签方式：（默认为0）0=增量打标签（分组单选为替换多选为新增），1=替换标签（当labels参数为空时，替换则清空原有标签）
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
     * 删除客户标签.
     *
     * @param int   $optUserId 操作员ID
     * @param array $crmIds    需要进行操作的客户ID，不超过10个，公海id操作无效
     * @param array $labelIds  需要删除的标签id列表，如果不传递，则清空客户的所有标签
     */
    public function deleteCrmLabels($optUserId, array $crmIds, array $labelIds = [])
    {
        $data = [
            'optUserId' => $optUserId,
            'crmIds'    => $crmIds,
            'labelIds'  => $labelIds,
        ];
        $response = $this->client('post', 'label/deleteCrmLabels', $data);

        return $response;
    }

    /**
     * 修改标签组名称.
     *
     * @param int    $optUserId    操作员ID
     * @param int    $labelGroupId 标签分组ID
     * @param string $name         标签分组名称
     */
    public function updateLabelGroupName($optUserId, $labelGroupId, $name)
    {
        $data = [
            'optUserId'    => $optUserId,
            'labelGroupId' => $labelGroupId,
            'name'         => $name,
        ];
        $response = $this->client('post', 'label/updateLabelGroupName', $data);

        return $response;
    }

    /**********企业联系人相关接口**********/
    // todo

    /**********推送通知相关接口**********/
    // todo

    /**********其他接口**********/
    // todo
}
