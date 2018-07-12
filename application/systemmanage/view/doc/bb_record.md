### bb_record表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned zerofill| |   |主键, 自增, | 视频id |
|uid| int(11)| |   || 用户ID |
|type| int(2)|是 |   || 该视频类型 1：秀场 2：邀约 3：个人验证 |
|video_path| varchar(500)|是 |   || 视频路径 |
|big_pic| varchar(255)|是 |   || 封面大图 |
|thumbnailpath| varchar(500)|是 |   || 视频的缩略图 |
|usersort| int(5)|是 |   || 用户类型 对应usersort表id |
|activity_id| int(11)|是 |   || 活动ID |
|room_id| varchar(255)|是 |   || 房间唯一ID |
|audit| int(2)|是 |  0 || 审核 0：未审核 1：通过审核 2：未通过 |
|like| int(11)|是 |  0 || 点赞数量 |
|look| int(11)|是 |  0 || 观看人数 |
|time| varchar(80)|是 |   || 创建视频时间 |
|address| varchar(80)|是 |  火星 || 录制该视频的地点 |
|title| varchar(255)|是 |   || 主题名称 |
|token| varchar(255)|是 |   ||  |
|heat| int(4)|是 |  0 || 热度 1为推荐 |
|label| varchar(50)|是 |   || 标签 |
|is_remove| int(1)|是 |  0 || 是否删除视频 0未删除 1删除 |
|longitude| float(10,6)|是 |  0.000000 || 经度 |
|latitude| float(10,6)|是 |  0.000000 || 纬度 |
|stealth| int(1)|是 |  0 || 是否隐身 |
|price| int(11)| |  0 || 波币购买价 |
|price_type| tinyint| |  1 || 1免费课程，2付费课程，3vip课程 |
