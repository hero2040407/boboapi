### bb_buy_video表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11)| |   |主键, 自增, | 主键 |
|uid| int(11)| |  0 || 用户ID |
|video_id| int(11)| |  0 || 视频id，注意有3张表 |
|video_table| varchar(255)| |   || bb_push直播表，bb_record录播表， |
|create_time| int(11)| |  0 || 生成时间 |
|price| int(11)| |  0 || 波币价格 |
