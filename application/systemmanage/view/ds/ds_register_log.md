### bb_register_log表
  
| 字段        | 类型 |    注释 |
| -------- |:------|:------|
|id| int(11) |   主键, 自增, |  
|ds_id| int(11) |  大赛id |  
|uid| int(11) |   报名者uid |  
|create_time| int(11) |   报名时间 |  
|has_join| int(11) |   是否参加过，即上传过视频 |  
|money| int(11) |   人民币费用，单位元 |  
|phone| int(11) |  手机号 |  
|sex| int(11) |   1男 ，0女 |  
|birthday| int(11) |   生日类似 2017-01 |  
|name| int(11) |   真实姓名 |  
|has_pay| int(11) |   1付过钱或大赛无需付钱，0未付钱 |  
|has_dangan| int(11) |  1填过档案或大赛无需填档案，0未填档案 |  
|pic| string | 个人照片  |  

