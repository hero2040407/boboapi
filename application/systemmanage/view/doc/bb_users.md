### bb_users表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|uid| int(11) unsigned| |   |主键, 自增, | 用户id |
|platform_id| varchar(255)| |   ||  |
|nickname| varchar(255)|是 |   ||  |
|pic| varchar(255)|是 |   || 头像 |
|phone| varchar(50)|是 |  0 || 手机号码 |
|device| varchar(255)|是 |   || 登录设备 |
|address| varchar(255)|是 |   || 登录地址 |
|login_type| int(4)|是 |   || 登录类型 1： 微信 2：QQ  3：手机 4：微博 |
|login_time| varchar(20)|是 |   || 登录时间 |
|login_count| int(11)|是 |   || 登录次数 |
|logout_time| varchar(20)|是 |   || 退出时间 |
|userlogin_token| varchar(50)|是 |   || 登录验证号 |
|sex| int(2)|是 |  0 || 性别 0：女的 1：男 |
|email| varchar(50)|是 |   || 邮箱 |
|birthday| varchar(40)|是 |   || 生日 |
|register_time| varchar(20)|是 |   || 注册时间 |
|attestation| int(1)|是 |  0 || 是否认证 0 未认证 1审核中 2认证成功 |
|sign_board| int(1)|是 |  0 || 当天是否签到 |
|series_sign_max| int(11)|是 |  0 || 连续签到最大次数 |
|series_sign| int(11)|是 |  0 || 累计签到次数 |
|permissions| int(2)|是 |  1 || 权限 1:正常用户 2：管理员 |
|specialty| varchar(50)|是 |   || 特长 |
|signature| varchar(255)|是 |   || 用户签名 |
|is_online| smallint(1)|是 |  0 || 是否在线 |
|longitude| float(10,6)|是 |  0.000000 || 经度 |
|latitude| float(10,6)|是 |  0.000000 || 纬度 |
|vip| smallint(1)|是 |  0 || 是不是VIP |
|vip_time| varchar(11)|是 |   || vip到期时间 |
|max_record_time| int(11)|是 |  120 || 默认最大录制时间 |
|min_record_time| int(11)|是 |  8 || 默认最小录制时间 |
|monster_count| int(11)|是 |  1 || 用户拥有的怪兽数量 |
|ranking| int(11)|是 |  0 || 排行榜名次 |
|user_agent| varchar(255)|是 |   || 登录版本跟设备信息 |
