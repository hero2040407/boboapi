### bb_focus表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|uid| int(11)| |   || 用户ID |
|focus_uid| int(11)| |   || 关注人 |
|time| varchar(11)|是 |   || 关注时间 |

*说明：例如我关注了张三，uid是我，focus_uid是张三的uid*
