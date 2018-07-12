### bb_money_prepare表
  
| 字段        | 类型 |    注释 |
| -------- |:------|:------|
|id| int(11) |   主键, 自增, |  
|uid| int(11) |  用户id |  
|phone| varchar | 手机号 |  
|order_no| varchar |  我们自己的订单号 |  
|ds_id| int(11) |  大赛id |  
|create_time| int(11) |  订单下单时间 |  
|has_success| int(11) |  0未验证 1 为成功 2为失败订单 |  
|terminal_type| int(11) |  暂未使用 |  
|third_name| varchar |  第三方支付名称 |  
|third_serial| varchar |  第三方支付订单号，反查第三方用 |  
|openid| varchar |  对应服务号的openid |  
|money| float |  报名费，单位元 |  

