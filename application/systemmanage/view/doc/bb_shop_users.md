### bb_shop_users表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|uid| int(11)| |   |主键, 自增, | 商家id |
|nickname| varchar(50)|是 |   || 昵称 |
|name| varchar(50)|是 |   || 姓名 |
|tel| varchar(50)|是 |   || 电话 |
|phone| varchar(50)| |   || 手机 |
|countries| varchar(50)|是 |  中国 || 国家 |
|province| varchar(50)| |   || 省 |
|city| varchar(50)| |   || 市 |
|area| varchar(50)| |   || 区 |
|street| varchar(500)|是 |   || 街道地址 |
|shop_name| varchar(50)|是 |   || 商铺名称 |
|divided_into_ratio| int(4)|是 |  100 || 分成比率100为全部分成 |
|sales_num| int(11)|是 |  0 || 销售总量 |
|price_num| int(11)|是 |  0 || 销售总金额 |
