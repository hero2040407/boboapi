### bb_question表
  
| 字段        | 类型 |    注释 |
| -------- |:------|:------|
|id| int(11) |   主键, 自增, |  
| ds_id | int(11) |   大赛id |  
| question | varchar |   名称 |  
| answer | varchar |  回答的内容 |  
| type | int(11) |   1公告，2问答 |  
| question_time | int(11) |   创建时间 |  
| question_uid | int(11) |  提问者uid |  
|answer_time| int |   回答时间 |  
|sort| int(11) |   排序，大的靠前 |  

