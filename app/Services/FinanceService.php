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
     protected $countryBaseUrl;
     protected $transferBaseUrl;
     protected $exchangeRateUrl;

     public function __construct()
     {
         $this->financeService  = env('FINANCE_BASE_URL');
         $this->countryBaseUrl  = env('COMMON_COUNTRY_URL');
         $this->transferBaseUrl = env('TRANSFER_BASE_URL');
         $this->exchangeRateUrl = env('EXCHANGE_RATE_URL');
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
     * 获取用户资产信息(根据coin_id)
     * @param $data array
     * @return array
     */
    public function getFinanceByCoin($data)
    {
        $url = "finance/finance?".http_build_query($data);
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
             $count['data']['count']=0;
         }
         //分页信息
         $page_info['count']=$count['data']['count'];//总数量

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
         $url = "finance/finance_wallet?".http_build_query($data);
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
        return $this->send_request($url, 'post',$data,$this->financeService);
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

    /**
     * 创建提现记录
     * @param $data array
     * @return array
     */
    public function createFinanceWithdraw($data)
    {
        $url = "finance/finance_withdraw";
        return $this->send_request($url, 'post',$data,$this->financeService);
    }

    /**
     * 获取币种列表
     * @param $data array
     * @return array
     */
    public function getCoinList($data)
    {
        $url = "finance/finance?".http_build_query($data);
        return $this->send_request($url, 'get',[],$this->financeService);
    }

    /**
     * 根据id获取币种
     * @param $id int
     * @return array
     */
    public function getCoin($id)
    {
        $url = "common/coin/id/".$id;
        return $this->send_request($url, 'get',[],$this->countryBaseUrl);
    }
    /**
     * 钱包划转OTC
     * @param $user_id int
     * @param $coin_id int
     * @param $amount
     * @return array
     */
    public function financeToOtc($user_id,$coin_id,$amount){
        $url = "finance/to_otc/user_id/{$user_id}/coin_id/{$coin_id}";
        return $this->send_request($url, 'post',['amount'=>$amount],$this->transferBaseUrl);
    }

    /**
     * 钱包划转币币
     * @param $user_id int
     * @param $coin_id int
     * @param $amount
     * @return array
     */
    public function financeToExchange($user_id,$coin_id,$amount){
        $url = "finance/to_exchange/user_id/{$user_id}/coin_id/{$coin_id}";
        return $this->send_request($url, 'post',['amount'=>$amount],$this->transferBaseUrl);
    }

    /**
     * 获取美元汇率
     * @return array
    */
    public function exchange()
    {
        $url = "common/exchange/exchange";
        return $this->send_request($url, 'get',[],$this->exchangeRateUrl);
    }

    /**
     * 虚拟币与美元汇率信息
     * @return array
     */
    public function coin()
    {
        $url = "common/exchange/coin";
        return $this->send_request($url, 'get',[],$this->exchangeRateUrl);
    }

}