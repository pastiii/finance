<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Token\Apiauth;

class BaseController extends Controller
{
    protected $user_id;

    public function __construct()
    {
        /*$info = ['user_name'=>'tom','user_id'=>256,'type'=>'user','email'=>'m15202471353@163.com'];
        $this->type = 1; // 1:pc 2:Mobile
        $this->token = Apiauth::login($info,$this->type);*/
        $this->user_id =intval(ApiAuth::userId());
    }

    /**
     *数据返回
     * @param $data
     * @param int $code
     * @return array
     */
    protected function response($data, $code = 0)
    {
        return [
            'status_code' => $code,
            'timestamp'   => time(),
            'data'        => $data,
        ];
    }

    /**
     * 错误数据返回
     * @param int $code
     * @return array
     */
    protected function errors($code = 0, $line = "")
    {
        return [
            'status_code' => $code,
            "line"        => $line,
            'timestamp'   => time(),
        ];
    }

    /**
     * 验证错误信息
     * @param $code
     * @return mixed
     */
    protected function code_num($code)
    {
        return config('state_code.' . $code);
    }

    /**
     * 根据获取用户信息
     * @return array
     */
    protected function get_user_info()
    {
        return ApiAuth::user();
    }

    /**
     * 获取token
     * @param $user_info
     */
    public function getToken($user_info)
    {
        return ApiAuth::login($user_info);
    }

}


?>

