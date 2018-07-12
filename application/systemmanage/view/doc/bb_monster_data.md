### bb_monster_data表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|uid| int(11)|是 |   ||  |
|monster_id| int(11)|是 |   ||  |
|state| bit(1)|是 |   ||  |
|equipment| char(1)|是 |   ||  |
|level| int(11)| |  1 ||  |
|exp| int(11)| |  0 ||  |
|like| int(11)| |  0 ||  |
|nextleveltime| varchar(24)| |  0 ||  |
|create_time| varchar(50)|是 |   || 创建时间 |
