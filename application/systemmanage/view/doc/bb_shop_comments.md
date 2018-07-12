### bb_shop_comments表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11)| |  0 |主键, |  |
|shop_id| int(11)|是 |  0 || 商品ID |
|score| int(2)|是 |  0 || 评分 |
|message| text|是 |   || 评论 |
|uid| int(11)|是 |   || 用户id |
|is_remove| smallint(1)|是 |  0 || 是否删除 |
