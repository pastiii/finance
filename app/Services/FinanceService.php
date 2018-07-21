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
     protected $validationBaseUrl;

     public function __construct()
     {
         $this->financeService= env('FINANCE_BASE_URL');
         $this->validationBaseUrl= env('VALIDATION_BASE_URL');
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
     public function getwalletAddr($data)
     {
         $url = "finance/finance_wallet/id/".$data['finance_wallet_id'];
         return $this->send_request($url, 'get',[],$this->financeService);
     }

     /**
     * 获取用户资产地址二维码
     * @param $addr string
     * @return array
     */
     public function getwalletAddrQRcode($addr)
     {
         $url = "captcha/qrcode/msg/{$addr}";
         return $this->send_request($url,'get','',$this->validationBaseUrl);
     }


}