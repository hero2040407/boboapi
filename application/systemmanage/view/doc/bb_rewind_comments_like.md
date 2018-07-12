### bb_rewind_comments_like表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|uid| int(11)|是 |   ||  |
|comments_id| int(11)|是 |   ||  |
|type| int(2)|是 |   || 1:表示评论 2:表示回复 |
