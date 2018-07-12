### bb_address表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|uid| int(11)| |  0 || 用户id |
|name| varchar(255)| |   || 收货人姓名 |
|phone| varchar(255)| |   || 收货人手机 |
|tel| varchar(255)| |   || 固话 |
|countries| varchar(255)| |  中国 || 国家名 |
|province| varchar(255)| |   || 省 |
|city| varchar(255)| |   || 市 |
|area| varchar(255)| |   || 区 |
|street| varchar(255)| |   || 街道 |
|is_default| smallint(1)|是 |  0 || 是否默认地址 |
|zip_code| varchar(255)| |   || 邮编 |
|time| int(11)| |  0 || 时间戳 |
|is_del| tinyint(4)| |  0 || 1假删除，0正常 |
