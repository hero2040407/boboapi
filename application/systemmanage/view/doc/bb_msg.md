### bb_msg表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|uid| int(11)|是 |   ||  |
|type| smallint(2)|是 |  0 || 系统类型 0用户消息 1系统消息 2升级消息 3获得宠物蛋消息 4 任务完成消息 5充值消息 |
|title| varchar(50)|是 |   ||  |
|info| varchar(1024)|是 |   ||  |
|img| varchar(255)|是 |   ||  |
|time| varchar(11)|是 |   ||  |
|is_read| smallint(1)|是 |  0 || 是否已经读取 |
|overdue_time| varchar(11)|是 |   || 过期时间 |
