<?php
/**
 * Created by PhpStorm.
 * User: zhl
 * Date: 2018/7/23
 * Time: 10:24
 */
namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Api\V1\BaseController;
use Dingo\Api\Http\Request;
use App\Services\OtcService;
use App\Services\UserService;

class OtcController extends BaseController
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
            'coin_id' => 'nullable|int|min:1'
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
            foreach ($list['data']['list'] as $value){
                $temp=[
                    'otc_finance_id'      => $value['otc_finance_id'],
                    'coin_id'             => $value['coin_id'],
                    'coin_name'           => $value['coin_name'],
                    'coin_type'           => $value['coin_type'],
                    'coin_image'          => '',
                    'finance_available'   => $value['finance_available_str'],
                    'finance_amount'      => $value['finance_amount_str'],
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
            'limit'   => 'required|int|min:1',
            'page'    => 'required|int|min:1',
            'coin_id' => 'nullable|int|min:1',
            'begin'   => 'nullable|int',
            'end'     => 'nullable|int'
        ]);
        $data['user_id']=$this->user_id;

        //获取用户OTC资产信息历史列表
        $list= $this->otcService->getOtcFinanceHistoryList($data);

        if($list['code'] != 200){
            $code=$this->code_num('GetMsgFail');
            return $this->errors($code,__LINE__);
        }

        return $this->response($list['data'], 200);;

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


}