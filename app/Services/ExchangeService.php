<?php
/**
 * Created by PhpStorm.
 * User: zhl
 * Date: 2018/7/23
 * Time: 10:48
 */
namespace App\Services;

use App\Support\ApiRequestTrait;

class ExchangeService
{
    use ApiRequestTrait;
    protected $exchangeService;

    public function __construct()
    {
        $this->exchangeService= env('EXCHANGE_BASE_URL');
    }

    /**
     * 获取用户OTC资产信息列表
     * @param $data array
     * @return array
     */
    public function getExchangeFinanceList($data)
    {
        //获取用户OTC资产信息列表数量
        $count=$this->getExchangeFinanceCount($data); //数量
        if($count['code'] != 200){
            return $count;
        }
        //分页信息
        $page_info['count']=$count['data']['count'];//总数量
        $page_info['current_page'] = intval($data['page']);//当前页
        $page_info['total_page'] =  ceil($count['data']['count']/$data['limit']);//每页显示数量

        $data['start']=($data['page']-1)*$data['limit'];
        $url = "exchange/exchange_finance?".http_build_query($data);
        $list = $this->send_request($url, 'get',[],$this->exchangeService);

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
    public function getExchangeFinanceCount($data)
    {
        unset($data['limit']);
        unset($data['page']);
        $url = "exchange/exchange_finance/count?".http_build_query($data);
        return $this->send_request($url, 'get',[],$this->exchangeService);
    }
    
    /**
     * 获取用户OTC资产信息历史列表
     * @param $data array
     * @return array
     */
    public function getExchangeFinanceHistoryList($data)
    {
        //获取用户OTC资产信息历史列表数量
        $count=$this->getExchangeFinanceHistoryCount($data); //数量
        if($count['code'] != 200){
            return $count;
        }
        //分页信息
        $page_info['count']=$count['data']['count'];//总数量
        $page_info['current_page'] = intval($data['page']);//当前页
        $page_info['total_page'] =  ceil($count['data']['count']/$data['limit']);//每页显示数量

        $data['start']=($data['page']-1)*$data['limit'];
        $url = "exchange/exchange_finance_history?".http_build_query($data);
        $list = $this->send_request($url, 'get',[],$this->exchangeService);

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
    public function getExchangeFinanceHistoryCount($data)
    {
        unset($data['limit']);
        unset($data['page']);
        $url = "exchange/exchange_finance_history/count?".http_build_query($data);
        return $this->send_request($url, 'get',[],$this->exchangeService);
    }

    /**
     * 获取用户OTC资产信息历史
     * @param $id int
     * @return array
     */
    public function getExchangeFinanceHistory($id)
    {
        $url = "exchange/exchange_finance_history/id/".$id;
        return $this->send_request($url, 'get',[],$this->exchangeService);
    }


}