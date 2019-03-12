<?php
/**
 * Created by PhpStorm.
 * User: derek
 * Date: 2018/10/17
 * Time: 下午2:16
 */

namespace app\lib\exception;

use Exception;
use think\exception\Handle;
use think\Request;
use think\log;

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;

    // 需要返回客户端当前请求的 URL 路径
    public function render(Exception $e)
    {
        if($e instanceof BaseException){
            //如果是自定义的异常
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;

        }else{
            if(config('app_debug')){
                //return default error page
                return parent::render($e);
            }else {
                $this->code = 500;
                $this->msg = 'server error';
                $this->errorCode = 999;
                $this->recordErrorLog($e);
            }
        }
        $request = new Request;
        $result = [
            'msg' => $this->msg,
            'errorCode' => $this->errorCode,
            'request_url' => $request->url()
        ];
        return json($result,$this->code);
    }
    private function recordErrorLog(Exception $e){
        Log::init([
            'type' => 'File',
            'path' => LOG_PATH,
            'level' => ['error']
        ]);
        Log::record($e->getMessage(), 'error');
    }
}
