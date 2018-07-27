<?php
/**
 * Created by PhpStorm.
 * User: zhl
 * Date: 2018/7/21
 * Time: 13:31
 */
namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Api\V1\Common\CommonController;
use App\Services\OtcService;
use App\Services\ExchangeService;
use Dingo\Api\Http\Request;
use App\Services\FinanceService;
use App\Services\UserService;
use App\Support\SaltTrait;
use Illuminate\Support\Facades\Redis;
use App\Services\SecurityVerificationService;

class FinanceController extends CommonController
{
     use SaltTrait;
     /* @var FinanceService  $financeService*/
     protected $financeService;
    /* @var UserService  $userService*/
     protected $userService;

     public function __construct()
     {
         parent::__construct();
         $this->getFinanceService();
         $this->getUserService();
     }

    /**
     * @return \Illuminate\Foundation\Application|mixed
     */
     protected function getFinanceService()
     {
        if (!isset($this->financeService)) {
            $this->financeService = app(FinanceService::class);
        }
        return $this->financeService;
     }

     protected  function getUserService()
     {
        if (!isset($this->userService)) {
            $this->userService = app(UserService::class);
        }
        return $this->userService;

     }

    /**
     * 获取用户基本信息
     * @return array
     */
    public function getUserInfo()
    {
        //获取用户创建时间
        $user=$this->get_user_info();

        $user_info = $this->userService->getUser($this->user_id);
        if ($user_info['code'] != 200) {
            $code  = $this->code_num('GetUserFail');
            return $this->errors($code, __LINE__);
        }
        //获取用户最后一次登录历史
        $page       = 1;
        $pageSize   = 1;
        $last_login = $this->userService->getUserLoginHistoryList($this->user_id, $pageSize, $page);
        //账号信息获取
        $email = $this->userService->getUserEmailById($this->user_id);
        $phone = $this->userService->getUserPhone($this->user_id);
        //数据处理
        $data['email']            = empty($email['data']) ? "" : $email['data']['email'];
        $data['phone']            = empty($phone['data']) ? "" : $phone['data']['phone_number'];
        $data['name']             = $user['user_name'];
        $data['last_login_time']  = isset($last_login['data']['list']) ? date('Y-m-d H:i:s', $last_login['data']['list'][0]['created_at']) : '';
        $data['finance_usdt']= 0;
        $data['finance_rmb']=0;
        $data['finance_us']=0;
        return $this->response($data, 200);
    }

     /**
     * 获取用户资产信息列表
     * @param Request $request
     * @return array
     */
     public function getFinanceList(Request $request)
     {
         $data=$this->validate($request, [
             'limit'   => 'required|int|min:1',
             'page'    => 'required|int|min:1',
             'coin_name' => 'nullable|string'
         ]);
         $data['user_id']=$this->user_id;
         //获取用户资产信息列表
         $list= $this->financeService->getFinanceList($data);

         if($list['code'] != 200){
             $code=$this->code_num('GetMsgFail');
             return $this->errors($code,__LINE__);
         }
         //重组数据
         $info=[];
         if(!empty($list['data']['list'])){
             foreach ($list['data']['list'] as $k=>$value){
                 $temp=[
                     'finance_id' => $value['finance_id'],
                     'coin_id'    => $value['coin_id'],
                     'coin_name'  => $value['coin_name'],
                     'coin_type'  => $value['coin_type'],
                     'coin_image' => '',
                     'finance_available' =>$k, //$value['finance_available_str'],
                     'finance_amount' =>$k,//$value['finance_amount_str'],
                     'finance_amount_rmb' => '0.0'
                 ];
                 array_push($info,$temp);
             }
         }
         $res['list']= $info;
         $res['page']=$list['data']['page'];

         return $this->response($res, 200);
     }

     /**
     * 获取用户钱包地址
     * @param Request $request
     * @return array
     */
     public function getFinanceWallet(Request $request)
     {
         $user=$this->get_user_info();
         $data=$this->validate($request, [
             'finance_id' => 'required|int|min:1'
         ]);
         //获取资产信息
         $finance_info=$this->financeService->getFinance($data['finance_id']);

         if(empty($finance_info['data'])){
             $code=$this->code_num('FinanceEmpty');
             return $this->errors($code,__LINE__);
         }

         $param= [
             "user_id"  =>$this->user_id,
             "user_name"=>$user['user_name'],
             "coin_id"  =>$finance_info['data']['coin_id'],
             "coin_name"=>$finance_info['data']['coin_name'],
             "coin_type"=>$finance_info['data']['coin_type']
         ];
         //获取地址信息
         $info=$this->financeService->createFinanceWallet($param);

         if(empty($info['data'])){
             $code=$this->code_num('InfoEmpty');
             return $this->errors($code,__LINE__);
         }

         $res['wallet_addr']=$info['data']['wallet_addr'];
         $res['qr_msg']=base64_encode($info['data']['wallet_addr']);
         return $this->response($res, 200);
     }

     /**
     * 获取用户资产信息历史列表
     * @param Request $request
     * @return array
     */
     public function getFinanceHistoryList(Request $request)
     {
         $data=$this->validate($request, [
             'limit'   => 'nullable|int|min:1',
             'page'    => 'nullable|int|min:1',
             'finance_id' => 'required|int|min:1'
         ]);
         $data['user_id']=$this->user_id;
         if(!isset($data['limit']))  $data['limit']=10;
         if(!isset($data['page']))  $data['page']=1;
         //获取用户资产信息历史列表
         $list= $this->financeService->getFinanceHistoryList($data);

         if($list['code'] != 200){
             $code=$this->code_num('GetMsgFail');
             return $this->errors($code,__LINE__);
         }

         //重组数据
         $info=[];
         if(!empty($list['data']['list'])){
             foreach ($list['data']['list'] as $value){
                 $temp=[
                     'finance_history_id'   => $value['finance_history_id'],
                     'created_at'           => date('Y-m-d H:s',$value['created_at']),
                     'coin_name'            => $value['coin_name'],
                     'amount'               => $value['amount'],
                     'finance_history_type' => $value['finance_history_type'],
                     'status'               => $value['status'],
                 ];
                 array_push($info,$temp);
             }

         }
         for($i=1;$i<5;$i++){
             $temp=[
                 'finance_history_id'   => $i,
                 'created_at'           => date('Y-m-d H:s',time()),
                 'coin_name'            => 'ETH',
                 'amount'               => 100,
                 'finance_history_type' => $i,
                 'status'               => 1,
             ];
             array_push($info,$temp);
         }
         $res['list']= $info;
         $res['page']=$list['data']['page'];

         return $this->response($res, 200);;

     }

    /**
     * 获取用户资产信息历史
     * @param Request $request
     * @return array
     */
     public function getFinanceHistory(Request $request)
     {
         $data=$this->validate($request, [
             'finance_history_id' => 'required|int|min:1'
         ]);

         $info=$this->financeService->getFinanceHistory($data['finance_history_id']);

         if(empty($info['data'])){
             $code=$this->code_num('InfoEmpty');
             return $this->errors($code,__LINE__);
         }

         return $this->response($info['data'], 200);
         
     }

    /**
     * 获取用户资产信息
     * @param Request $request
     * @return array
     */
     public function getFinance(Request $request)
     {

         $data=$this->validate($request, [
             'finance_id' => 'required|int|min:1',
         ]);
         //钱包信息
         $finance_info=$this->financeService->getFinance($data['finance_id']);
         if(empty($finance_info['data'])){
             $code=$this->code_num('FinanceEmpty');
             return $this->errors($code,__LINE__);
         }
         //获取币种信息
         /*$coin_info =$this->financeService->getCoin(2);//$finance_info['data']['coin_id']
         if(empty($coin_info['data'])){
             $code = $this->code_num('NetworkAnomaly');
             return $this->errors($code,__LINE__);
         }*/

         $ping_data = $this->userService->getUserPin($this->get_user_info());

         $ping_status = 1;
         if(empty($ping_data['data'])){
             $ping_status = 0;
         }
         $res['finance_available']=1000; //$finance_info['data']['finance_available'];
         $res['coin_status']  = 2;//$coin_info['data']['coin_status'];
         $res['finance_rate'] = 10;//$coin_info['data']['withdraw_fees'];//手续费
         $res['finance_upper']= 1000;//$coin_info['data']['withdrawone_max'];//单次提额上限
         $res['ping_status']  = $ping_status;
         $res['check_two']    = $this->checkTwoStatus();
         return $this->response($res, 200);
     }
     /**
     * 提交提现申请
     * @param Request $request
     * @return array
     */
     public function createFinanceWithdraw(Request $request)
     {
         $data=$this->validate($request, [
             'finance_id' => 'required|int|min:1',
             'destination_addr' => 'required|string',//目标地址
             'withdraw_amount' => 'required|string',
             'password' =>'required',
             'cation_type' => 'nullable|in:phone,email,google'
         ]);
         //钱包信息
         $finance_info=$this->financeService->getFinance($data['finance_id']);
         if(empty($finance_info['data'])){
             $code=$this->code_num('FinanceEmpty');
             return $this->errors($code,__LINE__);
         }
/*
         //获取币种信息
         $coin_info =$this->financeService->getCoin($finance_info['data']['coin_id']);
         if(empty($coin_info['data'])){
             $code = $this->code_num('NetworkAnomaly');
             return $this->errors($code,__LINE__);
         }
         //判断币种是否正常
         if($coin_info['data']['coin_status'] != 2){
             $code = $this->code_num('CoinStatus');
             return $this->errors($code,__LINE__);
         }
         //判断提现金额是否大于单次限制金额
         if($data['withdraw_amount'] > $coin_info['data']['withdrawone_max']){
             $code=$this->code_num('WithdrawOneMax');
             return $this->errors($code,__LINE__);
         }

         //判断提现金额是否大于可用余额
         if($data['withdraw_amount'] > $finance_info['data']['finance_available']){
             $code=$this->code_num('FinanceAvailable');
             return $this->errors($code,__LINE__);
         }*/

         //检查资金密码
         /*$pin_code=$this->checkPin($data['password']);
         if($pin_code !== true){
             return $this->errors($pin_code,__LINE__);
         }*/

         //二次验证
         if(isset($data['cation_type'])){
            if($data['cation_type'] != 'google'){
                $cation_data=$this->validate($request,[
                    'cation_code' => 'required',
                    'cation_key' => 'required'
                ]);
            }else{
                $cation_data=$this->validate($request,[
                    'cation_code' => 'required'
                ]);
            }
            switch ($data['cation_type']){
                case 'phone':
                    $phone_code = $this->validatePhoneCode($cation_data);
                    if($phone_code !== true){
                        return $this->errors($phone_code,__LINE__);
                    }
                    break;
                case 'email':
                    $email_code = $this->validateEmailCode($cation_data);
                    if($email_code !== true){
                        return $this->errors($email_code,__LINE__);
                    }
                    break;
                case 'google':
                    $google_code = $this->checkGoogleCode($cation_data);
                    if($google_code !== true){
                        return $this->errors($google_code,__LINE__);
                    }
                    break;
            }
         }else{
             //开启,禁用二次验证判断
             if (!empty($this->checkTwoStatus())) {
                 $code = $this->code_num('TwoVerification');
                 return $this->response($this->checkTwoStatus(), $code);
             }
         }

         //组装数据
         $withdraw_data=[
             'finance_id'  =>$finance_info['data']['finance_id'],
             'user_id'     =>$finance_info['data']['user_id'],
             'user_name'   =>$finance_info['data']['user_name'],
             'coin_id'     =>$finance_info['data']['coin_id'],
             'coin_name'   =>$finance_info['data']['coin_name'],
             'coin_type'   =>$finance_info['data']['coin_type'],
             'destination_addr'        =>$data['destination_addr'],
             'finance_withdraw_amount' =>intval($data['withdraw_amount']),
             'finance_withdraw_amount_str' =>$data['withdraw_amount'],
             'finance_withdraw_status' =>1
         ];
         //创建提现记录
         $res=$this->financeService->createFinanceWithdraw($withdraw_data);

         if($res['code'] != 200){
             $code=$this->code_num('CreateFailure');
             return $this->errors($code,__LINE__);
         }

         return $this->response($res['data'], 200);
     }

    /**
     * 验证资金密码
     * @param $pin string
     * @return int
     */
    public function checkPin($pin){

        $ping_data = $this->userService->getUserPin($this->get_user_info());

        if($ping_data['code'] !=200 || empty($ping_data['data'])){
            $code = $this->code_num('GetPinFail');
            return $code;
        }
        //验证密码
        $password = $this->checkPassword($pin,$ping_data['data']['pin'],$ping_data['data']['salt']);
        //判断密码是否正确
        if(!$password){
            $code = $this->code_num('PinError');
            return $code;
        }
        return true;
    }

    /**
     * 验证邮箱验证码
     * @param $data array
     * @return array
     */
    public function validateEmailCode($data)
    {
        //获取邮箱地址
        $email_info = $this->userService->getEmailById($this->user_id);
        if (empty($email_info['data'])) {
            $code = $this->code_num('NetworkAnomaly');
            return $code;
        }
        $redis_key = env('PC_EMAIL') . $email_info['data']['email'] . "_" . $data['cation_key'];

        //验证邮箱验证码是否过期
        if (empty(redis::get($redis_key))) {
            $code = $this->code_num('VerifyInvalid');
            return $code;
        }
        //验证邮箱验证码是否错误
        if (!hash_equals(redis::get($redis_key), $data['cation_code'])) {
            $code = $this->code_num('VerificationCode');
            return $code;
        }
        //清除redis 里面的数据
        redis::del($redis_key);

        return true;
    }
    /**
     * 验证手机验证码
     * @param $data array
     * @return array
     */
    public function validatePhoneCode($data)
    {
        //获取用户手机
        $phone_info = $this->userService->getUserPhone($this->user_id);
        if (empty($phone_info['data']['phone_number'])) {
            $code = $this->code_num('NetworkAnomaly');
            return $code;
        }
          //验证手机验证码
          $redis_key = env('PC_PHONE') . $phone_info['data']['phone_number'] . "_" . $data['cation_key'];
          //验证邮箱验证码是否过期
          if (empty(redis::get($redis_key))) {
              $code = $this->code_num('VerifyInvalid');
              return $code;
          }
          //验证手机验证码是否错误
          if (!hash_equals(redis::get($redis_key), $data['cation_code'])) {
              $code = $this->code_num('VerificationCode');
              return $code;
          }
          //清除redis 里面的数据
          redis::del($redis_key);

          return true;
    }

    /**
     * 验证google验证码
     * @param $param array
     * @return array
     */
    public function checkGoogleCode($param)
    {
        /* 验证验证码 */
        $data['verify']=$param['cation_code'];
        /* 获取用户信息 */
        $user_info = $this->get_user_info();
        /*  获取登陆用户的googleKey  */
        $googleAuthenticator = $this->userService->getUserGoogleAuth($user_info['user_id']);

        /* 不否存在secret */
        if (!isset($data['secret'])) {
            /* 判断用户是否绑定 */
            if ($googleAuthenticator['real_code'] != 200) {
                $code = $this->code_num('Unbound');
                return $code;
            }
            /* 重新赋值 */
            $data['secret'] = $googleAuthenticator['data']['google_key'];
        }
        /* @var  SecurityVerificationService $securityVerification*/
        $securityVerification = app(SecurityVerificationService::class);
        /* 验证googleVerify */
        $result = $securityVerification->checkGoogleVerify($data);
        /* 数据返回 */
        if ($result['data']['code'] != 200) {
            $code = $this->code_num('VerificationCode');
            return $code;
        }

        /* 验证成功没有绑定google key 则绑定 */
        if ($googleAuthenticator['real_code'] != 200) {
            $response = $this->bindingGoogleKey($user_info, $data['secret']);
            /* 判断是否绑定成功 */
            if (!$response) {
                $code = $this->code_num('BindingFail');
                return $code;
            }
        }

        if (!empty($googleAuthenticator['data']) && !empty($data['secret']))
        {
            //绑定过就修改
            $response = $this->editGoogleKey($data['secret']);
            /* 判断是否绑定成功 */
            if (!$response) {
                $code = $this->code_num('BindingFail');
                return $code;
            }
        }

        return true;
    }

    /**
     * 币种列表
     * @param Request $request
     * @return array
     */
    public function getFinanceCoinList(Request $request)
    {
        $data=$this->validate($request,[
            'finance_id' => 'required|int|min:1',
        ]);
        $finance_id=$data['finance_id'];
        //当前钱包币种资产信息
        $finance_info=$this->financeService->getFinance($data['finance_id']);
        if(empty($finance_info['data'])){
            $code=$this->code_num('FinanceEmpty');
            return $this->errors($code,__LINE__);
        }
        //获取当前钱包币种列表
        $info=$this->financeService->getCoinList($data);

        if($info['code'] != 200){
            $code=$this->code_num('GetMsgFail');
            return $this->errors($code,__LINE__);
        }

        $list=[];
        if(!empty($info['data']['list'])){
            foreach ($info['data']['list'] as $value){
                //默认选中
                $checked= $finance_id == $value['finance_id'] ? 1 : 0;
                $temp=[
                   'finance_id' => $value['finance_id'],
                   'coin_id'    => $value['coin_id'],
                   'coin_name'  => $value['coin_name'],
                   //'checked'    => $checked
                ];
                array_push($list,$temp);
            }
        }
        //转出账户
        $roll_out =['finance'];
        //转入账户
        $roll_in  =['exchange','otc'];
        //当前币种可用余额
        $roll_out_available=$finance_info['data']['finance_available'];//转出账户可用余额
        $roll_out_coin_name=$finance_info['data']['coin_name'];//转出账户币种名称

        //返回数据
        $res=[
            'list'=>$list,
            'roll_out' => $roll_out,
            'roll_in'  => $roll_in,
            'roll_out_available' => $roll_out_available,
            'roll_out_coin_name' => $roll_out_coin_name
        ];

        return $this->response($res, 200);
    }

    /**
     * 币种改变
     * @param Request $request
     * @return array
     */
    public function coinChange(Request $request)
    {
        $data=$this->validate($request,[
            'finance_id'   => 'required|int|min:1',
            'roll_in_finance' => 'required|string|in:otc,exchange'
        ]);

        //当前钱包币种资产信息
        $finance_info=$this->financeService->getFinance($data['finance_id']);
        if(empty($finance_info['data'])){
            $code=$this->code_num('FinanceEmpty');
            return $this->errors($code,__LINE__);
        }
        $roll_out_available=$finance_info['data']['finance_available'];//转出账户可用余额
        $roll_out_coin_name=$finance_info['data']['coin_name'];//转出账户币种名称

        $param=[
            'user_id'=> 1, //$this->user_id,
            'coin_id'=> 8 //$finance_info['data']['coin_id']
        ];
        switch ($data['roll_in_finance']){
            case 'otc':
                //otc钱包当前币种信息
                $otcService=app(OtcService::class);
                $otc_finance=$otcService->getOtcFinance($param);
                if($otc_finance['code'] != 200){
                    $code=$this->code_num('NetworkAnomaly');
                    return $this->errors($code,__LINE__);
                }
                if(empty($otc_finance['data']['list'])){
                    $code=$this->code_num('RollError');
                    return $this->errors($code,__LINE__);
                }
                $finance_data=current($otc_finance['data']['list']);
                $roll_in_available=$finance_data['finance_available'];//转入账户可用余额
                $roll_in_coin_name=$finance_data['coin_name'];       //转入账户币种名称
                break;
            case 'exchange':
                //币币钱包当前币种信息
                $exchangeService=app(ExchangeService::class);
                $exchange_finance=$exchangeService->getExchangeFinance($param);
                if($exchange_finance['code'] != 200){
                    $code=$this->code_num('NetworkAnomaly');
                    return $this->errors($code,__LINE__);
                }
                if(empty($exchange_finance['data']['list'])){
                    $code=$this->code_num('RollError');
                    return $this->errors($code,__LINE__);
                }
                $finance_data=current($exchange_finance['data']['list']);
                $roll_in_available=$finance_data['finance_amount'];//转入账户可用余额
                $roll_in_coin_name=$finance_data['coin_name'];//转入账户币种名称
                break;
            default:
                $roll_in_available =0;
                $roll_in_coin_name ='';
        }
        $info=[
            'roll_out_available' => $roll_out_available,
            'roll_out_coin_name' => $roll_out_coin_name,
            'roll_in_available'  => $roll_in_available,
            'roll_in_coin_name'  => $roll_in_coin_name
        ];
        return $this->response($info, 200);
    }


     /**
     * 划转
     * @param Request $request
     * @return array
     */
    public function financeShift(Request $request)
    {
        $data=$this->validate($request,[
            'roll_out_finance'=>'required|string|in:finance,otc,exchange',
            'roll_in_finance' =>'required|string|in:finance,otc,exchange',
            'coin_id'         =>'required|int|min:1',
            'amount'          =>'required'
        ]);
        return $this->response('', 200);
    }



}