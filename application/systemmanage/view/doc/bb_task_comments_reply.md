### bb_task_comments_reply表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|comments_id| int(11)| |   || 活动ID |
|content| varchar(1024)| |   || 内容 |
|time| varchar(11)| |   ||  |
|uid| int(11)| |   ||  |
|reply_count| int(11)|是 |  0 || 回复数量 |
|audit| int(2)|是 |  0 || 是否认证 0：未认证 1：认证 2：失败 |
|is_remove| int(1)|是 |  0 || 是否删除 |
