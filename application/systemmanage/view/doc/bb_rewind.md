### bb_rewind表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned zerofill| |   |主键, 自增, |  |
|uid| int(11)| |   ||  |
|start_time| varchar(50)|是 |   ||  |
|end_time| varchar(50)|是 |   ||  |
|rewind_url| varchar(255)|是 |   || 回播地址 |
|space_name| varchar(255)|是 |   || 空间名称 |
|stream_name| varchar(255)|是 |   || 流名称 |
|like| int(11)|是 |  0 || 点赞数量 |
|ip| varchar(255)|是 |   || 推流人的ip地址 |
|people| int(11)|是 |   || 观看人数 |
|event| varchar(50)|是 |  rewind_done || 回播状态rewind_done 没有回播 rewind 有回播 |
|bigpic| varchar(255)|是 |   || 封面图片 |
|room_id| varchar(50)|是 |   || 房间唯一ID |
|title| varchar(50)|是 |   || 主题 |
|label| varchar(50)|是 |   || 标签 |
|is_remove| int(1)|是 |  0 || 是否删除视频 0未删除 1删除 |
|sort| int(4)|是 |  0 || 直播类型 3玩啥 1学啥 2宝贝秀 |
|activity_id| int(4)|是 |  0 || 活动主题id |
|longitude| float(10,6)|是 |  0.000000 || 经度 |
|latitude| float(10,6)|是 |  0.000000 || 纬度 |
|audit| int(1)|是 |  0 || 回播认证 只有认证的视频才会显示出来 |
|is_save| int(1)|是 |  0 || 是否保存~1为保存 0为不保存 |
|is_vip| int(1)|是 |  0 || 是不是VIP |
|flowers| int(11)|是 |  0 || 鲜花数量 |
|price| int(11)| |  0 || 波币购买价 |
|address| varchar(255)| |   || 原来的直播地址 |
|price_type| tinyint| |  1 || 1免费课程，2付费课程，3vip课程 |
