<?php
/**
 * Created by PhpStorm.
 * User: derek
 * Date: 2019/3/20
 * Time: 10:42 AM
 */

namespace app\api\validate;


class DeviceStatusInfoUpdate extends BaseValidate
{
    protected $rule = [
        'status' => 'require',
        'timeStart' => 'require',
        'timeStop' => 'require',
        'msgId' => 'require',
        'deviceID' => 'require|number'
    ];
}