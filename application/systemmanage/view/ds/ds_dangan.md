### bb_dangan表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|ds_id| int(11) | |   |大赛id |  |
|uid| int(11) | |   |用户id |  |
|config_id| int | |   |配置id,对应ds_dangan_config表主键 |  |
| value     | varchar|  |     |用户填写内容，复选框填1，文本框填内容，上传填文件web路径|  |
|create_time| int(11)|是 |  0 | 创建时间 ||
|type| int(11)|是 |  0 | 同ds_dangan_config表，1复选框，2文本框，3上传，4简介 ||

