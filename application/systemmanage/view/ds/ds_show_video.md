### bb_show_video表
  
| 字段        | 类型 |    注释 |
| -------- |:------|:------|
|id| int(11) |   主键, 自增, |  
| ds_id | int(11) |   大赛id |  
| room_id | varchar |   房间id |  
| video_id | int(11) |   视频id，对应bb_record表主键和bb_push表主键 |  
| uid | int(11) |   用户id |  
| type | int(11) |   1直播，2短视频 |  
| create_time | int(11) |   创建时间 |
  

