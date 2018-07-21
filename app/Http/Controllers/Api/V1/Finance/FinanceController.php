<?php
/**
 * Created by PhpStorm.
 * User: zhl
 * Date: 2018/7/21
 * Time: 13:31
 */
namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Api\V1\BaseController;
use Dingo\Api\Http\Request;
use App\Services\FinanceService;

class FinanceController extends BaseController
{
     protected $financeService;

     public function __construct()
     {
         parent::__construct();
     }

     /**
     * 获取 financeService
     * @return FinanceService|\Illuminate\Foundation\Application|mixed
     */
     protected function getFinanceService()
     {
        if (!isset($this->financeService)) {
            $this->financeService = app(FinanceService::class);
        }
        return $this->financeService;
     }

     /**
     * 获取用户资产信息列表
     * @param Request $request
     * @return array
     */
     public function getFinanceList(Request $request)
     {
         $this->getFinanceService();

         $data=$this->validate($request, [
             'limit'   => 'required|int|min:1',
             'page'    => 'required|int|min:1',
             'coin_id' => 'nullable|int|min:1'
         ]);
         $data['user_id']=$this->user_id;
         //获取用户资产信息列表
         $list= $this->financeService->getFinanceList($data);

         if($list['code'] != 200){
             $code=$this->code_num('GetMsgFail');
             return $this->errors($code,__LINE__);
         }

         return $this->response($list['data'], 200);;
     }

     /**
     * 获取用户钱包地址
     * @param Request $request
     * @return array
     */
     public function getwalletAddr(Request $request){
         $this->getFinanceService();

         $data=$this->validate($request, [
             'finance_wallet_id' => 'nullable|int|min:1'
         ]);

         $addr=$this->financeService->getwalletAddr($data);

         if(empty($addr['data'])){
             $code=$this->code_num('Empty');
             return $this->errors($code,__LINE__);
         }

         return $this->response($addr['data'], 200);;
     }


}