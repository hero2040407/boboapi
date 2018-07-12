### bb_task表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|title| varchar(50)|是 |   || 任务名 |
|info| varchar(255)|是 |   || 任务描述 |
|type| smallint(2)|是 |  0 || 任务类型,默认为0代表活动任务 1.直播 2.玩啥 3.学啥 |
|reward_type| int(8)|是 |  0 || 奖励类型 0为Bo币 |
|reward_count| int(11)|是 |  0 || 奖励数量 |
|send_type| char(1)|是 |  0 || 发送方式 0为用户手动领取 |
|next_task| int(11)|是 |  0 || 触发的下一个任务ID |
|state| int(4)|是 |  0 || 是否为主线任务 1为主线 默认为0子线任务 |
|min_age| int(4)|是 |  0 || 最小年龄段 |
|max_age| int(4)|是 |  0 || 最大年龄段 |
|level| int(11)|是 |  0 || 等级限制 |
|label| varchar(50)|是 |   || 标签 |
|is_remove| smallint(1)|是 |  0 || 0为未删除 1为删除 |
