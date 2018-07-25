<?php
/**
 * Created by PhpStorm.
 * User: zhl
 * Date: 2018/7/23
 * Time: 14:35
 */
namespace App\Services;
use App\Support\ApiRequestTrait;

class UserService{
    use ApiRequestTrait;

    protected $authBaseUrl;

    public function __construct()
    {
        $this->authBaseUrl = env('AUTH_BASE_URL');
    }
    /**
     * 获取ping码
     * @param $user_info
     * @return array
     */
    public function getUserPin($user_info)
    {
        $url = "userauth/user_pin/id/" . $user_info['user_id'];
        return $this->send_request($url, 'get','',$this->authBaseUrl);
    }

    /**
     * 通过用户id获取用户信息
     * @param $id
     * @return array
     */
    public function getUser($id)
    {
        $url  = "userauth/user/id/" . $id;
        $data = $this->send_request($url, 'get','',$this->authBaseUrl);
        return $data;
    }

    /**
     * 用户登录历史
     * @param $user_id
     * @param $pageSize
     * @param $page
     * @return array
     */
    public function getUserLoginHistoryList($user_id, $pageSize, $page)
    {
        $page = ($page - 1) * $pageSize;
        $url = "userauth/user_login_history?user_id=" . $user_id . "&sort=user_login_history_id&order=DESC&limit =" . $pageSize . "&start=" . $page;
        return $this->send_request($url, 'get','',$this->authBaseUrl);
    }

    /**
     * 通过Id获取邮箱
     * @param $id
     * @return array
     */
    public function getUserEmailById($id)
    {
        $email_url = "userauth/user_email/id/" . $id;
        return $this->send_request($email_url, 'get','',$this->authBaseUrl);
    }

    /**
     * 获取用户手机号
     * @param $id
     * @return array
     */
    public function getUserPhone($id)
    {
        $url = "userauth/user_phone/id/" . $id;
        return $this->send_request($url, 'get','',$this->authBaseUrl);
    }

    /**
     * 获取用户状态
     * @param $id
     * @return array
     */
    public function getUserStatus($id)
    {
        $url = "userauth/user_status/id/" . $id;
        return $this->send_request($url, 'get','',$this->authBaseUrl);
    }

    /**
     * 开启,禁用二次验证,验证用户信息
     * @param $info
     * @param $id
     * @return mixed
     */
    public function bindingInfo($info, $id)
    {
        $data = [];
        if ($info['data']['second_phone_status'] == 1) {
            //获取用户手机信息
            $phone_info    = $this->getUserPhone($id);
            $data['phone'] = 'phone';
            $data['phone_number'] = empty($phone_info['data']['phone_number']) ? "" : $phone_info['data']['phone_idd']." ". substr($phone_info['data']['phone_number'] , 0 , 3)."******".substr($phone_info['data']['phone_number'], -2,2);
        }

        if ($info['data']['second_email_status'] == 1) {
            //获取用户邮箱
            $email_info    = $this->getUserEmailById($id);
            $data['email'] = 'email';
            $data['email_info'] = empty($email_info['data']['email']) ? "" : substr($email_info['data']['email'], 0, 3) . "*****" . strstr($email_info['data']['email'], "@", false);
        }
        if ($info['data']['second_google_auth_status'] == 1) {
            $data['google'] = 'google';
        }

        return $data;
    }

}