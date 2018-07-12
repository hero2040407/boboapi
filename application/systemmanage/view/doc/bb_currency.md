### bb_currency表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, |  |
|uid| int(11)| |  0 || 用户id |
|gold| int(11)| |  0 || 波币数量 |
|gold_income| int(11)|是 |  0 || 总收入 |
|flower| int(11)|是 |  0 || 小红花 |
|discount| int(11)|是 |  0 || 折扣卷 |
|monster| int(4)|是 |  0 || 有几个宠物蛋没有开 |
