<?php
namespace BBExtend\fix;



class MessageType
{
    
    const register             = 110 ; // 注册
    const canjia_huodong       = 111 ; // 参加活动
    const level_up             = 112 ; // 等级升级
    const shangbang            = 113 ; // 上榜，这个未用
    const huodong_wancheng     = 114 ; // 活动完成（实际是后台发奖）
    const video_checked        = 115 ; // 视频通过审核
    const video_no_checked     = 116 ; // 视频没有通过审核
    const jinyan               = 117 ; // 被禁言
    const wancheng_renwu       = 118 ; // 完成任务
    const shipin_beizan        = 119 ; // 视频被赞
    
    const shipin_beipinglun    = 120 ; // 视频被评论
    const shipin_beidashang    = 121 ; // 视频被打赏
    const beiguanzhu           = 122 ; // 被关注
    const idol_upload_video    = 123 ; // 爱豆上传视频
    const idol_zhibo           = 124 ; // 爱豆直播
    
    const shangcheng_duihuan   = 125 ; // 商城兑换
    const chongzhi             = 126 ; // 充值成功
    const goumai_shipin        = 127 ; // 购买视频
    const vip_xufei            = 128 ; // vip 续费
    const dasai_video_checked  = 129 ; // 大赛视频通过审核
    const dasai_video_no_checked  = 130 ; // 大赛视频没有通过审核
    
    const fenxiang             = 131 ; // 分享5次奖励
    
    const dasai_auto_register  = 150 ; // 大赛自动注册成新用户，送波币。
    const dasai_baoming_zhifu  = 151 ; // 大赛报名支付成功，再送波币。
    const idol_chengjiu        = 152 ; // 爱豆成就
    const video_to_hot         = 153 ; // 视频被标记成热门
    const benren_chengjiu      = 154 ; // 本人成就
    
    const zhubo_xiaxian        = 160 ; // 主播下线
    const zhuanpan_choujiang   = 170;  // 幸运转盘抽奖信息
    const yaoqing_zhuce        = 171 ; // 邀请他人注册成功
    const beiyaoqing_zhuce     = 172 ; // 被邀请人注册成功
    
    
    const yaoqing_dianping     = 173 ; // 邀请导师点评
    const daoshi_dianping      = 174 ; // 导师点评
    const yaoqing_zhuce_again  = 175 ; // 被邀请人注册成功后7日内认证成功
    const yaoqing_dianping_fail= 176 ; // 星推官点评内荣不合格，判定失败，发送消息给星推官
    
    const yaoqing_dianping_chexiao = 177 ; // 邀请点评后，删除视频，          撤销通知，仅限于审核过短视频，且未点评星推官。
    const dasai_message            = 180 ; // 大赛报名成功自动通知。晋级成功，手动消息。
//     const dasai_jinji              = 181 ; // 大赛晋级成功
//     const dasai_shoudong           = 182 ; // 大赛手动公告。
    
    const tonggao_baoming_success  = 190 ; // 通告报名成功
    
    
    const houtai_fasong        = 1000; // 后台发送
    const baoming_jiaofei      = 1001; // 报名缴费
    const test                 = 1010; // 测试用
    
   
}
