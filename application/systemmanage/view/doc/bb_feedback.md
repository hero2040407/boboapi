### bb_feedback表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|uid| int(11)| |   || 用户ID |
|content| varchar(2048)|是 |   || 内容 |
|contact| varchar(255)|是 |   || 联系方式 |
|user_agent| varchar(255)|是 |   || 应用名/版本号 (设备型号;iOS版本) |
|time| varchar(11)|是 |   || 发送时间 |
