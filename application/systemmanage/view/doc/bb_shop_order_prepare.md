### bb_shop_order_prepare表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11)| |   |主键, 自增, |  |
|uid| int(11)| |  0 || 用户ID |
|price| decimal(10,2)| |  0.00 || 总价 |
|goods_id| int(11)| |  0 || 一件商品id |
|serial| varchar(100)| |   || 订单号 |
|address_id| int(11)| |  0 || 地址ID |
|type| tinyint(4)| |  0 || 1现金，2bo币 |
|is_success| tinyint(4)| |  0 || 0未付款，1已付款且复制到正式订单表 |
|create_time| int(11)| |  0 || 创建时间 |
|update_time| int(11)| |  0 || 更新时间 |
|count| int(11)| |  0 || 商品数量 |
|model| varchar(255)| |   || 商品规格 |
|style| varchar(255)| |   || 商品样式 |
|terminal| varchar(255)| |   || 终端型号 |
|terminal_type| tinyint(4)| |  0 || 1ios，2安卓 |
|third_name| varchar(255)| |   || 第三方支付名称 |
|third_serial| varchar(255)| |   || 第三方支付订单号，反查第三方用 |
