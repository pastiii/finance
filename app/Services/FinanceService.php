<?php
/**
 * Created by PhpStorm.
 * User: admin1
 * Date: 2018/7/21
 * Time: 14:21
 */

namespace App\Services;

use App\Support\ApiRequestTrait;

class FinanceService
{
     use ApiRequestTrait;
     protected $financeService;

     public function __construct()
     {
         $this->financeService= env('FINANCE_BASE_URL');
     }

    /**
     * 获取用户资产信息
     * @param $id int
     * @return array
     */
    public function getFinance($id)
    {
        $url = "finance/finance/id/".$id;
        return $this->send_request($url, 'get',[],$this->financeService);
    }
     /**
     * 获取用户资产信息列表
     * @param $data array
     * @return array
     */
     public function getFinanceList($data)
     {
         //获取用户资产信息列表数量
         $count=$this->getFinanceCount($data); //数量
         if($count['code'] != 200){
             return $count;
         }
         //分页信息
         $page_info['count']=$count['data']['count'];//总数量
         $page_info['current_page'] = intval($data['page']);//当前页
         $page_info['total_page'] =  ceil($count['data']['count']/$data['limit']);//每页显示数量

         $data['start']=($data['page']-1)*$data['limit'];
         $url = "finance/finance?".http_build_query($data);
         $list = $this->send_request($url, 'get',[],$this->financeService);

         if($list['code'] == 200){
             $list['data']['page']=$page_info;
         }

         return $list;
     }

     /**
     * 获取用户资产信息列表数量
     * @param $data array
     * @return array
     */
     public function getFinanceCount($data)
     {
         unset($data['limit']);
         unset($data['page']);
         $url = "finance/finance/count?".http_build_query($data);
         return $this->send_request($url, 'get',[],$this->financeService);
     }

     /**
     * 获取用户资产地址
     * @param $data array
     * @return array
     */
     public function getFinanceWallet($data)
     {
         $url = "finance/finance_wallet/id/".$data['finance_wallet_id'];
         return $this->send_request($url, 'get',[],$this->financeService);
     }

    /**
     * 创建用户资产钱包
     * @param $data array
     * @return array
     */
    public function createFinanceWallet($data)
    {
        $url = "finance/finance_wallet";
        return $this->send_request($url, 'get',$data,$this->financeService);
    }

    /**
     * 获取用户资产信息历史列表
     * @param $data array
     * @return array
     */
    public function getFinanceHistoryList($data)
    {
        //获取用户资产信息历史列表数量
        $count=$this->getFinanceHistoryCount($data); //数量
        if($count['code'] != 200){
            return $count;
        }
        //分页信息
        $page_info['count']=$count['data']['count'];//总数量
        $page_info['current_page'] = intval($data['page']);//当前页
        $page_info['total_page'] =  ceil($count['data']['count']/$data['limit']);//每页显示数量

        $data['start']=($data['page']-1)*$data['limit'];
        $url = "finance/finance_history?".http_build_query($data);
        $list = $this->send_request($url, 'get',[],$this->financeService);

        if($list['code'] == 200){
            $list['data']['page']=$page_info;
        }

        return $list;
    }

    /**
     * 获取用户资产信息历史列表数量
     * @param $data array
     * @return array
     */
    public function getFinanceHistoryCount($data)
    {
        unset($data['limit']);
        unset($data['page']);
        $url = "finance/finance_history/count?".http_build_query($data);
        return $this->send_request($url, 'get',[],$this->financeService);
    }

    /**
     * 获取用户资产信息历史
     * @param $id int
     * @return array
     */
    public function getFinanceHistory($id)
    {
        $url = "finance/finance_history/id/".$id;
        return $this->send_request($url, 'get',[],$this->financeService);
    }


    public function createFinanceWithdraw($data)
    {
        $url = "finance/finance_withdraw";
        return $this->send_request($url, 'post',$data,$this->financeService);
    }


}