### bb_area表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11)| |   |主键, 自增, |  |
|postcode| varchar(255)| |   || 邮政编码，如210000 |
|name| varchar(255)| |   || 地区名称，如南京市 |
|level| tinyint(4)| |  0 || 1省，2市，3区，4街道 |
|parent| int(11)| |  0 || 父id |
|path| varchar(255)| |   || id路径，例如10,162，含当前id |
|wordpath| varchar(255)| |   || 文字路径，例如江苏省,南京市,玄武区 |
|amap_code| varchar(255)| |   || 高德地图编码 |
|shortpy| varchar(255)| |   || 例如xwq代表玄武区，每汉字只取拼音头字母 |
|fullpy| varchar(255)| |   || 例如xuanwuqu代表玄武区 |
