### bb_live_device表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|device_key| varchar(255)| |   || 设备ID |
|uid| int(11)|是 |   || 用户UID |
|time| varchar(255)|是 |   || 绑定时间 |
|device_ip| varchar(255)|是 |   || 绑定设备的IP地址 |
|device_type| varchar(50)|是 |   || 设备类型 |
|online| int(1)|是 |  0 || 是否在线 |
