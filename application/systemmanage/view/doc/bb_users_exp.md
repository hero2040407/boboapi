### bb_users_exp表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|uid| int(11)| |  0 |主键, |  |
|level| smallint(2)|是 |  1 || 等级 |
|exp| int(11)|是 |  0 || 当前经验 |
|next_exp| int(11)|是 |  0 || 下一级的经验 |
|login| int(4)|是 |  10 || 每日登录 |
|upload_pic| int(4)|是 |  5 || 上传头像 |
|attestaion| int(4)|是 |  0 || 完成直播认证 |
|userinfo| int(4)|是 |  0 || 资料完善 |
|push| int(4)|是 |  0 || 发起直播 |
|record| int(4)|是 |  0 || 发布短视频 |
|comments| int(4)|是 |  0 || 发布文字评论 |
|share| int(4)|是 |  0 || 分享 |
|share_other_user| int(4)|是 |  0 || 内容被他人分享 |
|invitation_register| int(4)|是 |  0 || 邀请好友注册 |
|activity_like| int(4)|是 |  0 || 活动点赞 |
|complete_task| int(4)|是 |  0 || 完成任务 |
|other_user_like| int(4)|是 |  0 || 被关注 |
|show_live_course| int(4)|是 |  0 || 点播课程 |
|shop| int(4)|是 |  0 || 商城购买/兑换 |
|time| varchar(11)|是 |   || 上一次同步的时间 |
|reset_time| varchar(11)|是 |   || 重置时间 |
