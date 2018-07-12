### bb_expression_package表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|title| varchar(20)|是 |  默认表情 ||  |
|pic| varchar(200)|是 |  0 || 表情包的封面 |
|url| varchar(200)|是 |  0 ||  |
|currency_type| int(4)|是 |  1 || 货币种类 默认为金币 |
|currency_num| int(4)|是 |  0 || 购买需要数量 0为免费 |
|heat_level| int(2)|是 |  0 || 0为普通 1 热门 2 推荐 |
