### bb_toppic表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|picpath| varchar(255)|是 |  0 ||  |
|title| varchar(50)|是 |  0 ||  |
|linkurl| varchar(100)|是 |  # ||  |
|sort_id| int(11)|是 |  1 ||  |
|addtime| int(11)|是 |  0 ||  |
|linktype| int(11)| |  0 ||  0 网址,  1是学啥 , 2是邀约, 3 是玩啥 |
|activity_id| int(11)| |  0 || 具体的活动id |
