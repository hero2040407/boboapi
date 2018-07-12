### bb_activity表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, | 活动id |
|title| varchar(20)|是 |  怪兽bobo || 活动名称 |
|info| varchar(500)|是 |  场馆描述相关内容还未添加! || 场馆描述 |
|reward_info| varchar(500)|是 |  福利通知相关内容还未添加! || 直播福利 |
|uid| int(11)|是 |   || 创建该活动的人 |
|pic| varchar(100)|是 |   || 场馆logo |
|like| int(11)|是 |  0 || 喜欢人数 |
|people| int(11)|是 |  0 || 场馆人数 |
|bigpic_list| varchar(4096)|是 |   || 轮播图片 |
|is_open| int(2)|是 |  1 || 是否开启该活动 |
|type| char(1)|是 |  1 || 活动类型 |
|is_rmd| int(1)|是 |  0 || 是否推荐 |
|user_group| mediumtext|是 |   || 参加该活动的用户群体 |
|address| varchar(80)|是 |  火星 || 录制该视频的地点 |
|longitude| float(10,6)|是 |  0.000000 || 经度 |
|latitude| float(10,6)|是 |  0.000000 || 纬度 |
|label| int(2)|是 |   || 标签 筛选使用 |
|time| varchar(11)|是 |   || 创建时间 |
|heat| int(4)|是 |  0 || 热度 |
|is_remove| smallint(1)|是 |  0 || 是否删除 |
|contact| varchar(50)|是 |   || 联系方式 |
