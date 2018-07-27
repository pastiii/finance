<?php
/**
 * Created by PhpStorm.
 * User: zhl
 * Date: 2018/7/23
 * Time: 10:24
 */
namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Api\V1\Common\CommonController;
use Dingo\Api\Http\Request;
use App\Services\OtcService;
use App\Services\UserService;
use App\Services\ExchangeService;
use App\Services\FinanceService;
class OtcController extends CommonController
{
    /* @var OtcService  $otcService*/

    protected $otcService;
    protected $userService;

    public function __construct()
    {
        parent::__construct();
        $this->getOtcService();
        $this->getUserService();
    }

    /**
     * @return \Illuminate\Foundation\Application|mixed
     */
    protected function getOtcService()
    {
        if (!isset($this->otcService)) {
            $this->otcService = app(OtcService::class);
        }
        return $this->otcService;
    }

    /**
     * @return \Illuminate\Foundation\Application|mixed
     */
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
     * 获取用户OTC资产信息列表
     * @param Request $request
     * @return array
     */
    public function getOtcFinanceList(Request $request)
    {
        $data=$this->validate($request, [
            'limit'   => 'required|int|min:1',
            'page'    => 'required|int|min:1',
            'coin_name' => 'nullable|string'
        ]);
        $data['user_id']=$this->user_id;
        //获取用户OTC资产信息列表
        $list= $this->otcService->getOtcFinanceList($data);

        if($list['code'] != 200){
            $code=$this->code_num('GetMsgFail');
            return $this->errors($code,__LINE__);
        }
        //数据重组
        $info=[];
        if(!empty($list['data']['list'])){
            foreach ($list['data']['list'] as $k=>$value){
                $k==2?$k=0:'';
                $temp=[
                    'otc_finance_id'      => $value['otc_finance_id'],
                    'coin_id'             => $value['coin_id'],
                    'coin_name'           => $value['coin_name'],
                    'coin_type'           => $value['coin_type'],
                    'coin_image'          => '',
                    'finance_available'   => $k,//$value['finance_available_str'],
                    'finance_amount'      => $k,//$value['finance_amount_str'],
                    'finance_amount_rmb'  => '0.0',
                    'frozen_capital'      => '0.0'
                ];
                array_push($info,$temp);
            }
        }
        $res['list']= $info;
        $res['page']=$list['data']['page'];

        return $this->response($res, 200);
    }
    
    /**
     * 获取用户OTC资产信息历史列表
     * @param Request $request
     * @return array
     */
    public function getOtcFinanceHistoryList(Request $request)
    {
        $data=$this->validate($request, [
            'limit'   => 'nullable|int|min:1',
            'page'    => 'nullable|int|min:1',
            'otc_finance_id' => 'required|int|min:1',
        ]);
        if(!isset($data['limit'])) $data['limit']=10;
        if(!isset($data['page'])) $data['page']=1;
        $data['user_id']=$this->user_id;

        //获取用户OTC资产信息历史列表
        $list= $this->otcService->getOtcFinanceHistoryList($data);


        if($list['code'] != 200){
            $code=$this->code_num('GetMsgFail');
            return $this->errors($code,__LINE__);
        }
        //重组数据
        $info=[];
        if(!empty($list['data']['list'])){
            foreach ($list['data']['list'] as $value){
                for($i=0;$i<2;$i++){
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
        }
        for($i=1;$i<3;$i++){
            $temp=[
                'finance_history_id'   => $i,
                'created_at'           => date('Y-m-d H:s',time()),
                'coin_name'            => 'ETH',
                'amount'               => 100,
                'finance_history_type' => $i,
                'status'               => $i,
            ];
            array_push($info,$temp);
        }
        $res['list']= $info;
        $res['page']=$list['data']['page'];

        return $this->response($res, 200);;

    }

    /**
     * 获取用户OTC资产信息历史
     * @param Request $request
     * @return array
     */
    public function getOtcFinanceHistory(Request $request)
    {
        $data=$this->validate($request, [
            'finance_history_id' => 'required|int|min:1'
        ]);

        $info=$this->otcService->getOtcFinanceHistory($data['finance_history_id']);

        if(empty($info['data'])){
            $code=$this->code_num('InfoEmpty');
            return $this->errors($code,__LINE__);
        }

        return $this->response($info['data'], 200);

    }

    /**
     * 币种列表
     * @param Request $request
     * @return array
     */
    public function getOtcFinanceCoinList(Request $request)
    {
        $data=$this->validate($request,[
            'otc_finance_id' => 'required|int|min:1',
        ]);
        $finance_id=$data['otc_finance_id'];
        //当前钱包币种资产信息
        $finance_info=$this->otcService->getOtcFinanceById($data['otc_finance_id']);
        if(empty($finance_info['data'])){
            $code=$this->code_num('FinanceEmpty');
            return $this->errors($code,__LINE__);
        }
        //获取当前钱包币种列表
        $info=$this->otcService->getCoinList($data);

        if($info['code'] != 200){
            $code=$this->code_num('GetMsgFail');
            return $this->errors($code,__LINE__);
        }

        $list=[];
        if(!empty($info['data']['list'])){
            foreach ($info['data']['list'] as $value){
                //默认选中
                $checked= $finance_id == $value['otc_finance_id'] ? 1 : 0;
                $temp=[
                    'otc_finance_id' => $value['otc_finance_id'],
                    'coin_id'    => $value['coin_id'],
                    'coin_name'  => $value['coin_name'],
                    //'checked'    => $checked
                ];
                array_push($list,$temp);
            }
        }
        //转出账户
        $roll_out =['otc'];
        //转入账户
        $roll_in  =['finance','exchange'];
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
    public function otcCoinChange(Request $request)
    {
        $data=$this->validate($request,[
            'otc_finance_id'   => 'required|int|min:1',
            'roll_in_finance'  => 'required|string|in:finance,exchange'
        ]);

        //当前钱包币种资产信息
        $finance_info=$this->otcService->getOtcFinanceById($data['otc_finance_id']);
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
            case 'finance':
                //钱包当前币种信息
                /* @var FinanceService  $financeService*/
                $financeService=app(FinanceService::class);
                $finance=$financeService->getFinanceByCoin($param);
                if($finance['code'] != 200){
                    $code=$this->code_num('NetworkAnomaly');
                    return $this->errors($code,__LINE__);
                }
                if(empty($finance['data']['list'])){
                    $code=$this->code_num('RollError');
                    return $this->errors($code,__LINE__);
                }
                $finance_data=current($finance['data']['list']);
                $roll_in_available=$finance_data['finance_available'];//转入账户可用余额
                $roll_in_coin_name=$finance_data['coin_name'];       //转入账户币种名称
                break;
            case 'exchange':
                //币币钱包当前币种信息
                /* @var ExchangeService  $exchangeService*/
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
    public function otcFinanceShift(Request $request)
    {
        $data=$this->validate($request,[
            //'roll_out_finance'=>'nullable|string|in:finance,otc,exchange',
            'roll_in_finance' =>'required|string|in:finance,exchange',
            'otc_finance_id'      =>'required|int|min:1',
            'amount'          =>'required'
        ]);
        //当前钱包币种资产信息
        $finance_info=$this->otcService->getOtcFinanceById($data['otc_finance_id']);
        if(empty($finance_info['data'])){
            $code=$this->code_num('NetworkAnomaly');
            return $this->errors($code,__LINE__);
        }
        //判断是否可划转
        if($finance_info['data']['coin_type'] == 2){
            $code=$this->code_num('CoinRoll');
            return $this->errors($code,__LINE__);
        }
        $coin_id=$finance_info['data']['coin_id'];

        switch ($data['roll_in_finance']){
            case 'finance':
                $res= $this->otcService->otcToFinance($this->user_id,$coin_id,$data['amount']);
                if($res['code'] != 200){
                    $code=$this->code_num('TransferError');
                    return $this->errors($code,__LINE__);
                }
                break;
            case 'exchange':
                $res= $this->otcService->otcToExchange($this->user_id,$coin_id,$data['amount']);
                if($res['code'] != 200){
                    $code=$this->code_num('TransferError');
                    return $this->errors($code,__LINE__);
                }
                break;
        }

        return $this->response('ok', 200);
    }

}