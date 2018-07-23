<?php
return [
    'UserUnique'           => 5000,  //用户名已存在
    'EmailUnique'          => 5001,  //用户邮箱已注册
    'CreateFailure'        => 5002,  //创建失败
    'VerificationCode'     => 5003,  //验证码错误
    'GetUserFail'          => 5004,  //获取用户信息失败
    'PasswordError'        => 5005,  //密码错误
    'FrequentOperation'    => 5006,  //操作过于频繁,稍后再试
    'SendFail'             => 5007,  //发送失败
    'UpdateFailure'        => 5008,  //修改失败
    'GetMsgFail'           => 5009,  //获取信息失败
    'TokenFail'            => 5010,  //token验证失败 需重新登录
    'authorization'        => 5011,  //授权失败
    'LoginFailure'         => 5012,  //登录失败
    'UpperLimit'           => 5013,  //已达上限
    'DeleteFailure'        => 5014,  //删除失败
    'Certified'            => 5015,  //已认证
    'GetPinFail'           => 5016,  //未设置Pin码
    'PinError'             => 5017,  //Pin错误
    'GetFileFail'          => 5018,  //获取文件失败
    'UploadFail'           => 5019,  //文件上传错误
    'GetPromoFail'         => 5020,  //获取推广者信息失败
    'Unbound'              => 5021,  //此手机号未绑定
    'NotNull'              => 5022,  //不可为空
    'NotBinDing'           => 5023,  //已绑定
    'PhoneFail'            => 5024,  //手机验证失败
    'RegisterClosed'       => 5025,  //注册窗口已关闭
    'BindingFail'          => 5026,  //google 绑定失败
    'GetVerify'            => 5027,  //获取验证码失败
    'IllegalRegister'      => 5028,  //请填写邀请码
    'Identical'            => 5029,  //请填写新手机号码
    'Empty'                => 5030,  //账户不存在
    'PayNameUnique'        => 5031,   //支付账号已存在
    'CertificateFailure'   => 5032,   //身份证号已存在
    'TwoVerification'      => 6001,   //需要验证
    'ArticleEmpty'         => 6002,   //文章分类不存在
    'ParamError'           => 6003,   //参数错误
    'TicketFail'           => 6004,   //工单不存在
    'PayFail'              => 6005,  //支付方式不存在
    'PhoneNull'            => 6006,  //未绑定手机号
    'IdentificationEmpty'  => 6007, //未实名认证
    'IdentificationStatus' => 6008, //实名认证未通过
    'AccountNull'          => 6009, //邮箱或手机号不能为空
    'VerifyInvalid'        => 6010, //验证码失效
    'PhoneUnique'          => 6011, //该手机号已注册
    'EmailNull'            => 6012, //请输入邮箱
    'CountryNull'          => 6013, //区号不能为空
    'PhoneEmpty'           => 6014, //请填写手机号
    'UserNameUnique'       => 6015, //用户名已存在
    'SendVerify'           => 6016, //请发送短信验证码
    'SendEmailVerify'      => 6017, //请发送邮箱验证码
    'SmsValidate'          => 6018, //请验证手机验证码
    'NetworkAnomaly'       => 6019, //网络异常
    'UserNameError'        => 6020, //用户名错误
    'SafeValidate'         => 6021, //请先进行安全验证

    'FinanceEmpty'         => 7001, //钱包信息为空
    'FinanceAvailable'     => 7002, //提现余额不足
];

