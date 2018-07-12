### bb_shop_config表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11)| |   |主键, 自增, | 主键 |
|bb_key| varchar(255)| |   || 英文键 |
|bb_value| varchar(2000)| |   || 值 |
|info| varchar(255)| |   || 中文说明 |
|create_time| int(11)| |  0 || 生成时间 |
|update_time| int(11)| |  0 || 更新时间 |
|admin_id| int(11)| |  0 || 管理员id |
