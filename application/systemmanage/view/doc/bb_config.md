### bb_config表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(1) unsigned| |   |主键, 自增, |  |
|servername| varchar(30)|是 |   ||  |
|picserver| varchar(30)|是 |   ||  |
|monsterani_url| varchar(100)|是 |   ||  |
|ucloudkey| varchar(50)|是 |   ||  |
|hls_live| varchar(50)|是 |   ||  |
|video_live| varchar(50)|是 |   ||  |
|rtmp_live| varchar(50)|是 |   ||  |
|show_live| varchar(50)|是 |   ||  |
|nodejs| varchar(50)|是 |   ||  |
|nodejs_device| varchar(50)|是 |   ||  |
|nodejs_chat| varchar(50)|是 |   ||  |
|vip_count| int(11)|是 |  500 || 购买VIP的价格 |
