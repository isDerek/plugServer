<?php
/**
 * Created by PhpStorm.
 * User: derek
 * Date: 2018/11/2
 * Time: 上午11:02
 */

namespace app\api\model;


use think\model\concern\SoftDelete;
use think\Request;

class DeviceStatus extends BaseModel
{
    protected $autoWriteTimestamp = true;
    use SoftDelete;

    // 获取所有设备状态
    public function getAllDeviceStatusInfo(){
        $request = new Request;
        $params = $request->only(['msgId']);
        $msgId = $params['msgId'];
        $allDeviceStatusInfo = (new self)->order('device_id asc')->select();
        if(!$allDeviceStatusInfo){
            throw new DeviceExistException(['msg'=>'没找到设备信息']);
        }
        foreach ($allDeviceStatusInfo as $data){
            $data->save(['msgId'=>$msgId]);
        }
        return $allDeviceStatusInfo;
    }
    // 更新设备状态
    public function putDeviceStatusInfo(){
        $request = new Request;
        $params = $request->only(['msgId','timeStart','timeStop','status','deviceID']);
        $msgId = $params['msgId'];
        $timeStart = $params['timeStart'];
        $timeStop = $params['timeStop'];
        $status = $params['status'];
        $deviceID = $params['deviceID'];
        $deviceStatusInfoByID = (new self)->where('device_id','=',$deviceID)->find();
        if($deviceStatusInfoByID){
                $deviceStatusInfoByID->save(['msgId' => $msgId], ['device_id' => $deviceID]);
                $deviceStatusInfoByID->save(['time_start' => $timeStart], ['device_id' => $deviceID]);
                $deviceStatusInfoByID->save(['time_stop' => $timeStop], ['device_id' => $deviceID]);
                $deviceStatusInfoByID->save(['status' => $status], ['device_id' => $deviceID]);
                return $deviceStatusInfoByID;
        }
        throw new DeviceExistException(['msg'=>'设备编号不存在']);

    }
    // 查看过滤设备状态信息
    public function getFilterDeviceStatusInfo(){
        $request = new Request;
        $params = $request->only(['msgId','deviceID']);
        $msgId = $params['msgId'];
        $deviceID = $params['deviceID'];
        $filterDeviceStatusInfo = (new self)->order('device_id asc')
            ->whereLike('device_id',$deviceID.'%')->select();
        if(!$filterDeviceStatusInfo){
            throw new DeviceExistException (['msg'=>'找不到设备注册匹配数据']);
        }
        foreach ($filterDeviceStatusInfo as $data) {
            $data->save(['msgId' => $msgId]);
        }
        return $filterDeviceStatusInfo;
    }
}