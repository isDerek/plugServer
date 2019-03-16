<?php
/**
 * Created by PhpStorm.
 * User: derek
 * Date: 2019/3/12
 * Time: 10:28 AM
 */

namespace app\api\controller\v1;
use app\api\service\UserToken;
use app\api\validate\TokenGet;

class Token
{
  public function getToken($user=''){
    (new TokenGet())->gocheck();
    $ut = new UserToken();
    $token = $ut->get($user);
    return [
        'token' => $token
    ];
}
}