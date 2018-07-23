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

}