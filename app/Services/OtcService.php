<?php
/**
 * Created by PhpStorm.
 * User: zhl
 * Date: 2018/7/23
 * Time: 10:48
 */
namespace App\Services;

use App\Support\ApiRequestTrait;

class OtcService
{
    use ApiRequestTrait;
    protected $otcService;
    protected $transferBaseUrl;
    public function __construct()
    {
        $this->otcService= env('OTC_BASE_URL');
        $this->transferBaseUrl = env('TRANSFER_BASE_URL');
    }

    /**
     * 获取用户资产信息(根据finance_id)
     * @param $id int
     * @return array
     */
    public function getOtcFinanceById($id)
    {
        $url = "otc/otc_finance?".$id;
        $res= $this->send_request($url, 'get',[],$this->otcService);
        return [
            'code' => 200,
            'data' => current($res['data']['list'])
        ];
    }

    /**
     * 获取用户资产信息(根据coin)
     * @param $data array
     * @return array
     */
    public function getOtcFinance($data)
    {
        $url = "otc/otc_finance?".http_build_query($data);
        return $this->send_request($url, 'get',[],$this->otcService);
    }

    /**
     * 获取用户OTC资产信息列表
     * @param $data array
     * @return array
     */
    public function getOtcFinanceList($data)
    {
        //获取用户OTC资产信息列表数量
        $count=$this->getOtcFinanceCount($data); //数量
        if($count['code'] != 200){
            return $count;
        }
        //分页信息
        $page_info['count']=$count['data']['count'];//总数量
        //$page_info['current_page'] = intval($data['page']);//当前页
       // $page_info['total_page'] =  ceil($count['data']['count']/$data['limit']);//每页显示数量

        //$data['start']=($data['page']-1)*$data['limit'];
        $url = "otc/otc_finance?".http_build_query($data);
        $list = $this->send_request($url, 'get',[],$this->otcService);

        if($list['code'] == 200){
            $list['data']['page']=$page_info;
        }

        return $list;
    }

    /**
     * 获取用户OTC资产信息列表数量
     * @param $data array
     * @return array
     */
    public function getOtcFinanceCount($data)
    {
        //unset($data['limit']);
        //unset($data['page']);
        $url = "otc/otc_finance/count?".http_build_query($data);
        return $this->send_request($url, 'get',[],$this->otcService);
    }
    
    /**
     * 获取用户OTC资产信息历史列表
     * @param $data array
     * @return array
     */
    public function getOtcFinanceHistoryList($data)
    {
        //获取用户OTC资产信息历史列表数量
        $count=$this->getOtcFinanceHistoryCount($data); //数量
        if($count['code'] != 200){
            return $count;
        }
        //分页信息
        $page_info['count']=$count['data']['count'];//总数量
        $page_info['current_page'] = intval($data['page']);//当前页
        $page_info['total_page'] =  ceil($count['data']['count']/$data['limit']);//每页显示数量

        $data['start']=($data['page']-1)*$data['limit'];
        $url = "otc/otc_finance_history?".http_build_query($data);
        $list = $this->send_request($url, 'get',[],$this->otcService);

        if($list['code'] == 200){
            $list['data']['page']=$page_info;
        }

        return $list;
    }

    /**
     * 获取用户OTC资产信息历史列表数量
     * @param $data array
     * @return array
     */
    public function getOtcFinanceHistoryCount($data)
    {
        unset($data['limit']);
        unset($data['page']);
        $url = "otc/otc_finance_history/count?".http_build_query($data);
        return $this->send_request($url, 'get',[],$this->otcService);
    }

    /**
     * 获取用户OTC资产信息历史
     * @param $id int
     * @return array
     */
    public function getOtcFinanceHistory($id)
    {
        $url = "otc/otc_finance_history/id/".$id;
        return $this->send_request($url, 'get',[],$this->otcService);
    }

    /**
     * 获取币种列表
     * @param $data array
     * @return array
     */
    public function getCoinList($data)
    {
        $url = "otc/otc_finance?".http_build_query($data);
        return $this->send_request($url, 'get',[],$this->otcService);
    }

    /**
     * OTC划转钱包
     * @param $user_id int
     * @param $coin_id int
     * @param $amount
     * @return array
     */
    public function otcToFinance($user_id,$coin_id,$amount){
        $url = "otc/to_finance/user_id/{$user_id}/coin_id/{$coin_id}";
        return $this->send_request($url, 'post',['amount'=>$amount],$this->transferBaseUrl);
    }

    /**
     * OTC划转币币
     * @param $user_id int
     * @param $coin_id int
     * @param $amount
     * @return array
     */
    public function otcToExchange($user_id,$coin_id,$amount){
        $url = "otc/to_exchange/user_id/{$user_id}/coin_id/{$coin_id}";
        return $this->send_request($url, 'post',['amount'=>$amount],$this->transferBaseUrl);
    }

}