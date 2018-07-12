### bb_task_user表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|uid| int(11)|是 |   ||  |
|complete_task_group| varchar(4096)|是 |  0 || 已经完成过的任务 |
|complete| varchar(50)|是 |  0,0,0 || 是否完成 |
|reward| varchar(50)|是 |  0,0,0 || 是否可以领取奖励0为不可以领取1为可以领取2为已经领取 |
|time| varchar(20)|是 |   ||  |
|task_group| varchar(255)|是 |  1,2,3 || 当前任务列表 |
|refresh_time| varchar(20)|是 |  0 || 刷新时间 |
