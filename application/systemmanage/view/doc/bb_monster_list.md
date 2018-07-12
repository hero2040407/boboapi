### bb_monster_list表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(4) unsigned| |   |主键, 自增, |  |
|name| varchar(20)|是 |   ||  |
|info| varchar(255)|是 |   || 描述 |
|type| char(2)|是 |  1 ||  |
|pic_url| varchar(50)|是 |  0 || 形象地址 |
|icon| varchar(50)|是 |   || 动画地址 |
|eggpic_url| varchar(50)|是 |   || 蛋地址 |
|author_uid| int(11)|是 |  0 || 作者UID |
|author| varchar(50)|是 |  怪兽BoBo || 作者名称 |
|author_icon| varchar(255)|是 |   || 作者头像 |
|author_img| varchar(255)|是 |   || 作者原画 |
|like| int(11)|是 |  0 || 喜欢的人数 |
|level| int(5)|是 |  1 || 携带所需等级 |
