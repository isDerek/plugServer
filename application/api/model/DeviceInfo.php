<?php
/**
 * Created by PhpStorm.
 * User: derek
 * Date: 2018/10/31
 * Time: 下午2:08
 */

namespace app\api\model;


use app\lib\exception\DeviceExistException;
use app\lib\exception\DeviceRegisterDeleteException;
use app\lib\exception\ManufacturerExistException;
use think\Request;
use think\model\concern;

class DeviceInfo extends BaseModel
{
    protected $autoWriteTimestamp = true;
    use concern\SoftDelete;

    // 注册设备信息
    public function postDeviceInfo(){
        $request = new Request;
        $params = $request->only(['msgId','deviceID','manufacturerID','deviceMAC','versionID','deviceAddr']);
        $msgId = $params['msgId'];
        $deviceID = $params['deviceID'];
        $manufacturerID = $params['manufacturerID'];
        $deviceMAC = $params['deviceMAC'];
        $versionID = $params['versionID'];
        $deviceAddr = $params['deviceAddr'];

        $deviceInfoByDID = (new self)->where('device_id','=',$deviceID)
            ->find();

        // 如果设备 ID 未被注册，则允许注册设备
        if(!$deviceInfoByDID){
            $orderID = (new ManufacturerInfo)->where('order_id','=',$manufacturerID)->find();

            if($orderID) {
                $deviceInfo = (new self)->data([
                    'device_id' => $deviceID,
                    'device_mac' => $deviceMAC,
                    'version_id' => $versionID,
                    'manufacturer_id' => $manufacturerID,
                    'device_addr' => $deviceAddr,
                    'msgId' => $msgId
                ]);
                $deviceInfo->save();
                // 将必要信息保存到设备状态数据库
                (new DeviceStatus)->data([
                    'device_id' => $deviceID,
                    'mac_id' => $deviceMAC,
                    'vendor_id' => $manufacturerID,
                    'device_addr' => $deviceAddr,
                    'msgId' => $msgId,
                    'status' => '离线'
                ])->save();
                return $deviceInfo;
            }else{
                throw new ManufacturerExistException(['msg'=>'厂商 ID 不存在']);
            }
        }
        throw new DeviceExistException();
    }
    // 获取注册设备页面的所有设备信息
    public function getAllDeviceInfo(){
        $request = new Request;
        $params = $request->only(['msgId']);
        $msgId = $params['msgId'];
        $allDeviceInfo = (new self)->order('device_id asc')->select();
        if(!$allDeviceInfo){
            throw new DeviceExistException(['msg'=>'没找到设备信息']);
        }
        foreach ($allDeviceInfo as $data){
            $data->save(['msgId'=>$msgId]);
        }
        return $allDeviceInfo;
    }
    // 更新注册设备信息
    public function putDeviceInfo(){
        $request = new Request;
        $params = $request->only(['msgId','deviceID','deviceMAC','versionID','manufacturerID','deviceAddr']);
        $msgId = $params['msgId'];
        $deviceID = $params['deviceID'];
        $deviceMAC = $params['deviceMAC'];
        $manufacturerID = $params['manufacturerID'];
        $versionID = $params['versionID'];
        $deviceAddr = $params['deviceAddr'];
        $deviceInfoByID = (new self)->where('device_id','=',$deviceID)->find();
        if($deviceInfoByID){
            $orderID = (new ManufacturerInfo)->where('order_id','=',$manufacturerID)->find();
            if($orderID) {
                $deviceInfoByID->save(['msgId' => $msgId], ['device_id' => $deviceID]);
                $deviceInfoByID->save(['manufacturer_id' => $manufacturerID], ['device_id' => $deviceID]);
                $deviceInfoByID->save(['device_mac' => $deviceMAC], ['device_id' => $deviceID]);
                $deviceInfoByID->save(['device_addr' => $deviceAddr], ['device_id' => $deviceID]);
                $deviceInfoByID->save(['version_id' => $versionID], ['device_id' => $deviceID]);
                $deviceStatusInfoByID = (new DeviceStatus)->where('device_id','=',$deviceID)->find();
                $deviceStatusInfoByID->save(['msgId' => $msgId], ['device_id' => $deviceID]);
                $deviceStatusInfoByID->save(['vendor_id' => $manufacturerID], ['device_id' => $deviceID]);
                $deviceStatusInfoByID->save(['mac_id' => $deviceMAC], ['device_id' => $deviceID]);
                $deviceStatusInfoByID->save(['device_addr' => $deviceAddr], ['device_id' => $deviceID]);
                return $deviceInfoByID;
            }else{
                throw new ManufacturerExistException(['msg'=>'厂商 ID 不存在']);
            }
        }
        throw new DeviceExistException(['msg'=>'设备编号不存在']);
    }
    // 根据设备 id 和厂商 id 筛选注册设备信息
    public function filterDeviceRegisterInfo(){
        $request = new Request;
        $params = $request->only(['msgId','deviceID','manufacturerID']);
        $msgId = $params['msgId'];
        $deviceID = $params['deviceID'];
        $manufacturerID = $params['manufacturerID'];
        $filterDeviceRegisterInfo = (new self)->order('device_id asc')->whereLike('manufacturer_id','%'.$manufacturerID.'%')
            ->whereLike('device_id',$deviceID.'%')->select();
        if(!$filterDeviceRegisterInfo){
            throw new DeviceExistException (['msg'=>'找不到设备注册匹配数据']);
        }
        foreach ($filterDeviceRegisterInfo as $data) {
            $data->save(['msgId' => $msgId]);
        }
        return $filterDeviceRegisterInfo;
    }
    // 删除设备注册信息
    public function deleteDeviceRegisterInfo(){
        $request = new Request;
        $params = $request->only(['msgId','deviceID']);
        $msgId = $params['msgId'];
        $deviceID = $params['deviceID'];
        $deviceRegisterByID = (new self)->where('device_id','=',$deviceID)->find();
        $deviceStatusByID = (new DeviceStatus())->where('device_id','=',$deviceID)->find();
        if(!$deviceRegisterByID&&!$deviceStatusByID){
            throw new DeviceRegisterDeleteException();
        }
        $deviceRegisterByID->delete();
        $deviceStatusByID->delete();
        return $msgId;
    }

}