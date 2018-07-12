### bb_buy表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|uid| int(11)|是 |   ||  |
|order| varchar(50)|是 |   || 订单号 |
|count| int(11)|是 |  0 || 购买的数量 |
|product_id| varchar(10)|是 |   || 购买的类型 1：bo币  |
|time| varchar(11)|是 |   || 订单时间 |
|receipt| mediumtext|是 |   || 收据 |
|successful| smallint(1)|是 |  0 || 0为未验证 1 为成功 2为失败订单 |
|error| text|是 |   || 错误消息 |
|terminal_type| tinyint(4)| |  1 || 终端类型，1ios，2安卓 |
|third_name| varchar(255)| |   || 第三方支付名称 |
|third_serial| varchar(255)| |   || 第三方支付订单号，反查第三方用 |
