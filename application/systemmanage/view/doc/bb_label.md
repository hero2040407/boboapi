### bb_label表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|name| varchar(16)|是 |   ||  |
|num| int(11)|是 |  0 || 多少人使用这个标签 |
|is_show| smallint(1)|是 |  1 || 是否显示 |
|image| varchar(255)|是 |   || 图片地址 |
