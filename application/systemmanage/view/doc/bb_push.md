### bb_push表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned zerofill| |   |主键, 自增, |  |
|uid| int(11)| |   ||  |
|event| varchar(50)|是 |  0 || 推拉流的状态事件 publish表示推流 publish_done表示断流 |
|push_url| varchar(255)|是 |   || 推流地址 |
|pull_url| varchar(255)|是 |   || 拉流地址 |
|space_name| varchar(255)|是 |   || 空间名称 |
|stream_name| varchar(255)|是 |   || 流名称 |
|ip| varchar(255)|是 |   || 推流人的ip地址 |
|like| int(11)|是 |   || 点赞人数 |
|people| int(11)|是 |   || 观看人数 |
|bigpic| varchar(255)|是 |   || 封面图片 |
|title| varchar(255)|是 |   ||  |
|label| varchar(255)|是 |   ||  |
|sort| int(4)|是 |  2 || 直播类型 3玩啥 1学啥 2宝贝秀 |
|activity_id| int(4)|是 |  0 || 活动主题id |
|address| varchar(120)|是 |   || 地址 |
|room_id| varchar(50)|是 |   || 房间号 |
|time| varchar(50)|是 |   ||  |
|heat| int(4)|是 |  0 || 热度 1为首屏推荐  |
|longitude| float(10,6)|是 |  0.000000 || 经度 |
|latitude| float(10,6)|是 |  0.000000 || 纬度 |
|stealth| int(1)|是 |  0 || 是否隐身 |
|flowers| int(11)|是 |  0 || 鲜花数量 |
|price| int(11)| |  0 || 波币购买价 |
