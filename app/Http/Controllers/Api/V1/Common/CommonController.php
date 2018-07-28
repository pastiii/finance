<?php

namespace App\Http\Controllers\Api\V1\Common;

use App\Http\Controllers\Api\V1\BaseController;
use App\Services\SecurityVerificationService;
use App\Services\MessageTemplateService;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Redis;

/**
 * Created by PhpStorm.
 * User: zxq
 * Date: 2018/7/9
 * Time: 21:12
 */
class CommonController extends BaseController
{
    /* @var SecurityVerificationService */
    protected $securityVerificationService;
    /* @var UserService  $userService*/
    protected $userService;
    /**
     * @return SecurityVerificationService|\Illuminate\Foundation\Application|mixed
     */
    protected function getSecurityVerificationService()
    {
        if (!isset($this->securityVerificationService)) {
            $this->securityVerificationService = app(SecurityVerificationService::class);
        }
        return $this->securityVerificationService;
    }

    protected  function getUserService()
    {
        if (!isset($this->userService)) {
            $this->userService = app(UserService::class);
        }
        return $this->userService;

    }

    /**
     * 获取token
     * @return array
     */
    public function getCaptcha()
    {
        $this->getSecurityVerificationService();
        //获取验证码token
        $data = $this->securityVerificationService->createToken();

        if ($data['real_code'] != 200) {
            $code = $this->code_num('GetVerify');
            return $this->errors($code, __LINE__);
        }
        return $this->response($data['data']['data']);
    }

    /**
     * 验证验证码
     * @param Request $request
     * @return array
     */
    public function checkCode(Request $request)
    {
        $this->getSecurityVerificationService();
        $data = $this->validate($request, [
            'code'  => 'required|string',
            'token' => 'required|string'
        ]);

        //获取验证信息
        $info = $this->securityVerificationService->checkCaptcha($data);

        if ($info['real_code'] == 200 && $info['data']['code'] != 200) {
            $code = $this->code_num('VerificationCode');
            return $this->errors($code, __LINE__);
        }

        if ($info['real_code'] == 200 && $info['data']['code'] == 200) {
            return $this->response("", 200);
        }

        $code = $this->code_num('GetMsgFail');
        return $this->errors($code, __LINE__);
    }

    /**
     * 发送邮件(改pin)
     * @return array
     */
    public function email()
    {
        //获取邮箱address
        $user_info = $this->get_user_info();
        //发送邮件
        $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);
        $time = 10;
        /* @var MessageTemplateService $MessageTemplateService 验证服务接口 */
        $MessageTemplateService = app(MessageTemplateService::class);
        $data['content']        = $MessageTemplateService->emailCopyWriting($code, $time);
        $data['subject']        = "EmailCode";
        $data['name']           = "HangZhou";
        $this->getSecurityVerificationService();
        $email_data = $this->securityVerificationService->sendEmail($user_info['email'], $data, $code);

        if ($email_data['code'] == 200) {
            return $this->response([ 'email_key' => $email_data['email_key'] ], 200);
        }

        $code = $this->code_num('SendFail');
        return $this->errors($code, __LINE__);
    }


    /**
     * 发送手机验证码
     * @param Request $request
     * @return mixed
     */
    public function sendSms(Request $request)
    {
        //判断手机验证的环境
        $phone_info = $this->validate($request, [
            'phone_number' => 'required|regex:/^1[34578]\d{9}$/',
            'phone_idd'    => 'required|string',
        ]);
        /* @var MessageTemplateService $MessageTemplateService 验证服务接口 */
        $MessageTemplateService = app(MessageTemplateService::class);
        $data                   = $MessageTemplateService->phoneCodeCopyWriting($phone_info['phone_idd']);


        $this->getSecurityVerificationService();
        $result = $this->securityVerificationService->sendSms($phone_info, $data);
        if ($result['code'] == 200) {
            return $this->response([ 'verification_key' => $result['verification_key'] ], 200);
        }

        $code = $this->code_num('SendFail');
        return $this->errors($code, __LINE__);
    }


    /**
     * 获取国家区域信息
     * @return array
     */
    public function getCountry()
    {
        /**var SecurityVerificationService $SecurityVerificationService*/
        $securityVerificationService = app(SecurityVerificationService::class);
        $country                     = $securityVerificationService->getCountyr();

        if ($country['real_code'] == 200) {
            return $this->response($country['data'], 200);
        }

        $code = $this->code_num('GetMsgFail');
        return $this->errors($code, __LINE__);
    }

    /**
     * 检查二次验证状态
     * @return mixed|string
     */
    protected function checkTwoStatus()
    {
        $info = '';
        $this->getUserService();
        //开启,禁用二次验证判断
        $redis_key = env('PC_STATUS') . "user_" . $this->user_id;
        if (empty(Redis::get($redis_key))) {
            $user_status = $this->userService->getUserStatus($this->user_id);
            if (!empty($user_status['data'])) {
                $info = $this->userService->bindingInfo($user_status, $this->user_id);
            }
        }
        return $info;
    }

    /**
     * 检查二次验证状态
     * @return mixed|string
     */
    public function checkTwo()
    {
        //开启,禁用二次验证判断
        if (!empty($this->checkTwoStatus())) {
            $code = $this->code_num('TwoVerification');
            return $this->response($this->checkTwoStatus(), $code);
        }

        return $this->response("", 200);
    }

    /**
     * 绑定google 信息
     * @param $user_data
     * @param $secret
     * @return bool
     */
    protected  function bindingGoogleKey ($user_data, $secret)
    {

        $google_data['user_id']    = intval($user_data['user_id']);
        $google_data['user_name']  = $user_data['user_name'];
        $google_data['google_key'] = $secret;
        /* 创建用户google_key */
        $response = $this->userService->createUserGoogleAuth($google_data);
        /* 授权失败 */
        if ($response['code'] != 200) {
            return false;
        }
        return true;
    }
}
