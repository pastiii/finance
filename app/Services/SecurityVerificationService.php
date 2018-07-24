<?php

namespace App\Services;
use App\Support\ApiRequestTrait;
use Illuminate\Support\Facades\Redis;

class SecurityVerificationService
{
    use ApiRequestTrait;

    protected $validationBaseUrl;
    protected $tokenBaseUrl;
    protected $sendBaseUrl;
    protected $countryBaseUrl;

    public function __construct()
    {
        $this->validationBaseUrl = env('VALIDATION_BASE_URL');
        $this->tokenBaseUrl      = env('TOKEN_BASE_URL');
        $this->sendBaseUrl      = env('SEND_BASE_URL');
        $this->countryBaseUrl = env('COMMON_COUNTRY_URL');

    }

    /**
     * 获取google secret qrcode
     * @param $sessionId
     * @param $secret
     * @return array
     */
    public function getGoogleSecret($sessionId,$user_name,$secret='')
    {
        $data['sessionId'] = $sessionId;
        $data['Authorization'] = 'token';
        $data['showName'] = $user_name;
        if (!empty($secret)) {
            $data['secret'] = $secret;
        }
        $url = 'captcha/googleauth/secret';
        return $this->send_request($url,'post',$data,$this->validationBaseUrl);
    }

    /**
     * 获取sessionId
     */
    public function getSessionId()
    {
        $url = 'captcha/googleauth/sessionid?Authorization=token';
        return $this->send_request($url,'post','',$this->validationBaseUrl);
    }

    /**
     * 验证google验证码
     * @param $data
     * @return array
     */
    public function checkGoogleVerify($data)
    {
        $url = 'captcha/googleauth/verify/'.$data['verify'].'/secret/'.$data['secret'];
        return $this->send_request($url,'get','',$this->validationBaseUrl);
    }

    /**
     * 获取验证token
     * @return array
     */
    public function createToken()
    {
        $url = "captcha/captcha?Authorization=token";
        return $this->send_request($url, 'post',"",$this->validationBaseUrl);
    }

    /**
     * 获取验证码
     * @param $data
     * @return array
     */
    public function getCaptchaCode($data)
    {
        $url      = "captcha/captcha/token/".$data['data']['data']['token']."?Authorization=token&output=base64";
        $response = $response = $this->send_request($url, 'get', "", $this->tokenBaseUrl);
        return ['captcha' => "data:image/png;base64,".$response['data']['data']['image'],'token' => $data['data']['data']['token']];
    }

    /**
     * 验证验证码
     * @param $data
     * @return array
     */
    public function checkCaptcha($data)
    {
        $ip = $this->getIp();
//        $ClientIp = ['ClientIp' => $ip];
        $url = "captcha/captcha/code/".$data['code']."/token/".$data['token']."?Authorization=token";

        $headers = ['ClientIp' => $ip];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->validationBaseUrl.$url);
        $result = curl_exec($ch);
        curl_close($ch);
        $email_data = json_decode($result, true);
        return $email_data;
        print_r($result);
        exit;
        echo $result;







        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_POST, 0);



        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $ClientIp);
        curl_setopt($ch, CURLOPT_URL, $this->validationBaseUrl.$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        return $result;
       // $email_data = json_decode($result, true);

//        if ($email_data['code'] == 200 && $data['type'] == 2) {
//            return true;
//        }
//
//        if ($email_data['code'] == 200 && $data['type'] == 4) {
//            return true;
//        }
//
//
//        $url = "captcha/captcha/code/".$data['code']."/token/".$data['token']."?Authorization=token";
//        if (strlen($data['code']) > 4) {
//            return $this->send_request($url, 'get', "", $this->validationBaseUrl, $ClientIp);
//        }
//        return $this->send_request($url, 'get', "", $this->validationBaseUrl);
    }

    /**
     * 获取真实ip
     * @return array|false|string
     */
    public function getIp()
    {
        //判断服务器是否允许$_SERVER
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $real_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $real_ip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $real_ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            //不允许就使用getenv获取
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $real_ip = getenv("HTTP_X_FORWARDED_FOR");
            } elseif (getenv("HTTP_CLIENT_IP")) {
                $real_ip = getenv("HTTP_CLIENT_IP");
            } else {
                $real_ip = getenv("REMOTE_ADDR");
            }
        }

        return $real_ip;
    }

    /**
     * 发送邮件
     * @param $email
     * @param $data
     * @return array
     */
    public function sendEmail($email, $data)
    {

        //邮件内容
        $data['email']   = $email;
        $data['subject'] = "EmailCode";
        $data['name']    = "HangZhou";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $this->sendBaseUrl."notify/email");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $email_data = json_decode($result, true);

        if ($email_data['code'] == 200) {
            $key       = str_random(15);
            $redis_key = env('PC_EMAIL') . $email . "_" . $key;
            //将email code存入redis
            $time = env('SEND_EMAIL_TIME') > 0 ? env('SEND_EMAIL_TIME') * 60 : 10 * 60;
            redis::setex($redis_key, $time, $data['code']);
            return ['email_key' => $key, 'code' => 200];
        }

        return ['code' => 403];
    }

    /**
     * 发送手机code
     * @param $phone_info
     * @param $data
     * @return array|bool
     */
    public function sendSms($phone_info, $data)
    {
        $data['phone'] = $phone_info['phone_idd'] . $phone_info['phone_number'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $this->sendBaseUrl."notify/sms");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $email_data = json_decode($result, true);

        if ($email_data['code'] == 200 && $data['type'] == 2) {
            return true;
        }

        if ($email_data['code'] == 200 && $data['type'] == 4) {
            return true;
        }

        if ($email_data['code'] == 200) {
            $key       = str_random(15);
            $redis_key = env('PC_PHONE') .$phone_info['phone_idd']. $phone_info['phone_number'] . "_" . $key;
            //将email code存入redis
            $time = env('SEND_PHONE_TIME') > 0 ? env('SEND_PHONE_TIME') * 60 : 10 * 60;
            redis::setex($redis_key, $time, $data['code']);
            return ['verification_key' => $key, 'code' => 200];
        }


        return ['code' => 403];
    }

    /**
     * 获取国家区域信息
     * @param $limit
     * @param $start
     * @return array
     */
    public function getCountyr($limit,$start){
        $url = 'common/country?limit='.$limit."&start".$start;
        return $this->send_request($url, 'get', [], $this->countryBaseUrl);
    }

}