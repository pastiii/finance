<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** @var Dingo\Api\Routing\Router $api */
$api = app('Dingo\Api\Routing\Router');


/* 公共模块 */
$api->version('v1', function ($api) {
    /** @var Dingo\Api\Routing\Router $api */
    $api->group([ 'namespace' => 'App\Http\Controllers\Api\V1\Common', 'prefix' => 'v1/common' ], function ($api) {
        /** @var Dingo\Api\Routing\Router $api */
        $api->group([ 'middleware' => 'apiauth' ], function ($api) {
            /** @var Dingo\Api\Routing\Router $api */
            $api->get('email', 'CommonController@email');
        });
        $api->get('get_captcha', 'CommonController@getCaptcha');
        $api->post('check_captcha', 'CommonController@checkCode');
        $api->post('send_sms', 'CommonController@sendSms');
        $api->post('check_phone_code', 'CommonController@validatePhoneCode');
        $api->get('get_country', 'CommonController@getCountry');
        $api->get('get_exchange_rate','CommonController@exchangeRateInfo');
    });
});


/* 钱包 */
$api->version('v1', function ($api) {
    /** @var Dingo\Api\Routing\Router $api */
    $api->group(['namespace' => 'App\Http\Controllers\Api\V1\Finance','prefix' => 'v1/finance'], function ($api) {
        /** @var Dingo\Api\Routing\Router $api */
        $api->group(['middleware'=>'apiauth'],function($api){
            /** @var Dingo\Api\Routing\Router $api */
            $api->get('user_info', 'FinanceController@getUserInfo');//获取用户资产信息列表
            $api->get('get_finance', 'FinanceController@getFinance');//获取用户资产信息
            $api->get('finance_list', 'FinanceController@getFinanceList');//获取用户资产信息列表
            $api->get('finance_wallet', 'FinanceController@getFinanceWallet');//获取用户钱包地址信息
            $api->get('finance_history_list', 'FinanceController@getFinanceHistoryList');//获取用户资产信息历史列表
            $api->get('finance_history', 'FinanceController@getFinanceHistory');//获取用户资产信息历史
            $api->post('create_finance_withdraw', 'FinanceController@createFinanceWithdraw');//提交提现申请
            $api->get('finance_shift', 'FinanceController@financeShift');//划转
            $api->post('create_finance_shift','FinanceController@financeShift');//提交划转
            $api->get('get_finance_coin_list','FinanceController@getFinanceCoinList');//币种列表
            $api->get('coinChange','FinanceController@coinChange');//划转币种改变
            $api->post('create_finance_shift','FinanceController@financeShift');//提交划转
            $api->post('set_amount_status','FinanceController@setAmountStatus');//设置
            /* OTC钱包 */
            $api->get('otc_user_info', 'OtcController@getUserInfo');//获取用户资产信息列表
            $api->get('otc_finance_list', 'OtcController@getOtcFinanceList');//获取用户OTC资产信息列表
            $api->get('otc_finance_history_list', 'OtcController@getOtcFinanceHistoryList');//获取用户OTC资产信息历史列表
            $api->get('otc_finance_history', 'OtcController@getOtcFinanceHistory');//获取用户OTC资产信息历史
            $api->get('otc_finance_coin_list','OtcController@getOtcFinanceCoinList');//币种列表
            $api->get('otc_coinChange','OtcController@otcCoinChange');//划转币种改变
            $api->post('otc_finance_shift','OtcController@otcFinanceShift');//提交划转
            /*币币交易*/
            $api->get('exchange_user_info', 'ExchangeController@getUserInfo');//获取用户资产信息列表
            $api->get('exchange_finance_list', 'ExchangeController@getExchangeFinanceList');//获取用户OTC资产信息列表
            $api->post('exchange_finance_history_list', 'ExchangeController@getExchangeFinanceHistoryList');//获取用户OTC资产信息历史列表
            $api->get('exchange_finance_history', 'ExchangeController@getExchangeFinanceHistory');//获取用户OTC资产信息历史
            $api->get('exchange_finance_coin_list','ExchangeController@getExchangeFinanceCoinList');//币种列表
            $api->get('exchange_coinChange','ExchangeController@exchangeCoinChange');//划转币种改变
            $api->post('exchange_finance_shift','ExchangeController@exchangeFinanceShift');//提交划转
        });
    });
});


