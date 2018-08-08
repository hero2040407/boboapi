<?php
namespace BBExtend\fix;

class TableType
{
//     10代表波豆。1代表波币。11代表积分
    const bb_currency_log__type_bobi = 1;
    const bb_currency_log__type_dan  = 2;
    const bb_currency_log__type_bodou = 10;
    const bb_currency_log__type_jifen = 11;
    
    const bb_users__attestation_weirenzheng = 0; // 未认证
    const bb_users__attestation_shenhezhong = 1; // 审核中
    const bb_users__attestation_chenggong = 2;   // 认证成功
    
//     登录类型 1： 微信， 2：QQ，  3：手机， 4：微博，5：机器人
    const bb_users__login_type_weixin  = 1;
    const bb_users__login_type_qq      = 2;
    const bb_users__login_type_shouji  = 3;
    const bb_users__login_type_weibo   = 4;
    const bb_users__login_type_jiqiren = 5;
    
    
    // 权限  1:正常用户， 2:管理员， 3:特邀用户， 4:机构用户，  99:机器人,
    // 10 300百万， 11 300万
    const bb_users__permissions_zhengchang = 1;
    const bb_users__permissions_guanliyuan = 2;
    const bb_users__permissions_teyao = 3;
    const bb_users__permissions_jigou = 4;
    const bb_users__permissions_zhenshi1 = 10;
    const bb_users__permissions_zhenshi2 = 11;
    const bb_users__permissions_jiqiren = 99;
    
    // 订单类型，1现金，2波币，3碎片兑换，4积分兑换
    const bb_shop_order__type_xianjin = 1;
    const bb_shop_order__type_bobi = 2;
    const bb_shop_order__type_suipian = 3;
    const bb_shop_order__type_jifen = 4;
    
    // 轮播图表
    const bb_toppic__sort_id_shouye = 2;
    const bb_toppic__sort_id_brandshop = 6;
    
    // 短视频类型//视频类型 //秀场 1   邀约 2  个人验证 3，      4是大赛，6通告，7动态
    const bb_record__type_xiuchang = 1;
    const bb_record__type_yaoyue = 2;
    const bb_record__type_yanzheng = 3;
    const bb_record__type_dasai = 4;
    const bb_record__type_advise = 6;
    const bb_record__type_updates = 7;
    
    
    // 短视频审核状态，0未审核，1已审核成功，2审核失败。
    const bb_record__audit_weishenhe = 0;
    const bb_record__audit_yishenhe = 1;
    const bb_record__audit_shenheshibai = 2;
    
    
    
    // 星推官点评表    点评状态     1，已邀请未点评 2，已点评未审核 3，审核过
    const bb_record_invite_starmaker__type_yiyaoqing = 1;
    const bb_record_invite_starmaker__type_yidianping = 2;
    const bb_record_invite_starmaker__type_yishenhe = 3;
    
    // 星推官点评表   点评类型  1文字，2短视频，3语音
    const bb_record_invite_starmaker__answer_type_wenzi = 1;
    const bb_record_invite_starmaker__answer_type_duanshipin = 2;
    const bb_record_invite_starmaker__answer_type_yuyin = 3;
    
    // 邀请注册状态 ,0 被邀请人未注册，但登记了手机号，1，已注册，2，被邀请人已领奖
    const bb_users_invite_register__is_complete_weizhuce = 0;
    const bb_users_invite_register__is_complete_yizhuce = 1;
    const bb_users_invite_register__is_complete_yilingjiang = 2;
    
    // 抽奖奖品类型，1波币，2表情包, 3谢谢参与，4再来一次，5实物奖品
    const lt_roulette__lt_type_bobi = 1;
    const lt_roulette__lt_type_biaoqingbao = 2;
    const lt_roulette__lt_type_xiexie = 3;
    const lt_roulette__lt_type_zailai = 4;
    const lt_roulette__lt_type_shiwu = 5;
    
    // 转盘类型，1签到转盘，2商户转盘
    const lt_roulette__type_qiandao = 1;
    const lt_roulette__type_shanghu = 2;
    
    // 配置表,11天降红包
    const bb_config_str__type_tianjiang = 11;
    
    
}



