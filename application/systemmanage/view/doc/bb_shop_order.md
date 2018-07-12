### bb_shop_order表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|uid| int(11)| |   || 用户ID |
|address_id| int(11)| |   || 地址ID |
|logistics| varchar(50)|是 |   || 物流号 |
|ems| int(4)|是 |   || 物流编码 |
|id| int(11)| |   |主键, 自增, |  |
|price| decimal(10,2)| |  0.00 || 订单总金额 |
|type| tinyint(4)| |  0 || 1现金，2波币 |
|goods_id| int(11)| |  0 || 商品id |
|serial| varchar(100)| |   || 订单号 |
|is_success| tinyint(4)| |  0 || 0待定，1成功付款 |
|terminal| varchar(255)| |   || 终端型号 |
|create_time| int(11)| |  0 || 生成时间 |
|update_time| int(11)| |  0 || 更新时间 |
|count| int(11)| |  0 || 商品数量 |
|model| varchar(255)| |   || 规格 |
|style| varchar(255)| |   || 样式 |
|third_name| varchar(255)| |   || 第三方支付名称 |
|third_serial| varchar(255)| |   || 第三方支付订单号，反查第三方用 |
|logistics_company| varchar(255)| |   || 物流公司代号，如SF |
|logistics_is_subscribe| tinyint(4)| |  0 || 物流单号轨迹是否订阅，1已订阅 |
|logistics_state| tinyint(4)| |  0 || 当前的物流单号轨迹状态，0-无轨迹，2-在途中,3-签收,4-问题件 |
|logistics_is_order| tinyint(4)| |  0 || 是否给物流公司下单成功 |
|logistics_is_pickup| tinyint(4)| |  0 || 物流公司是否上门取件 |
|logistics_is_complete| tinyint(4)| |  0 || 用户是否签收 |
|terminal_type| tinyint(4)| |  0 || 1ios，2安卓 |
|is_user_delete| tinyint(4)| |  0 || 是否用户删除 |
