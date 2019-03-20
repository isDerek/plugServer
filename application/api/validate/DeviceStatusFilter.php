<?php
/**
 * Created by PhpStorm.
 * User: derek
 * Date: 2019/3/20
 * Time: 5:02 PM
 */

namespace app\api\validate;


class DeviceStatusFilter extends BaseValidate
{
    protected $rule = [
        'deviceID' => 'number',
        'msgId' => 'require'
    ];
}