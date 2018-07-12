### bb_shop_logistics_trace表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11)| |   |主键, 自增, |  |
|uid| int(11)| |  0 || 用户id |
|order_no| varchar(255)| |   || 商城订单号 |
|logistics| varchar(255)| |   || 物流单号 |
|company| varchar(255)| |   || 物流公司代号 |
|create_time| int(11)| |  0 || 创建时间,就是接收快递鸟推送的时间 |
|accept_time| varchar(255)| |   || 轨迹信息1：时间 |
|accept_station| varchar(500)| |   || 轨迹信息2，事件 |
|remote_addr| varchar(255)| |   || 远程ip |
