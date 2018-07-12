### bb_task_activity表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, | 任务活动ID |
|title| varchar(50)| |   || 主题名称 |
|info| text| |   || 主题描述 |
|room_id| varchar(50)|是 |   || 视频演示ID 对应bb_record中的id |
|reward_info| varchar(500)|是 |   || 奖励描述 |
|is_send_reward| int(1)|是 |  0 || 是否发放奖励 0 为未发放 1为发放 |
|reward_id| varchar(100)|是 |  0 || 奖品的ID |
|type| int(11)|是 |  0 || 活动类别 0:擂台 1:活动 2:代言招募 |
|value| int(11)|是 |   || 根据类别不同则值所代表的意思不同 |
|bigpic_list| varchar(1024)|是 |  [{"picpath":"default.jpg","title":"0","linkurl":"#"}] || 轮播图片数组 json数组格式 |
|user_list| mediumtext|是 |   || 参加活动人员列表 |
|reward| int(11)|是 |  0 || bo币奖励 |
|task_id| int(11)|是 |  0 || 对应任务id |
|min_age| int(4)|是 |  0 || 最小年龄段 |
|max_age| int(4)|是 |  0 || 最大年龄段 |
|level| int(11)|是 |  0 || 等级限制 |
|start_time| varchar(14)|是 |   || 开始时间 |
|end_time| varchar(14)|是 |   || 结束活动时间 |
|sex| int(1)|是 |  0 || 0女1男 2未设定 |
|is_show| smallint(1)|是 |  1 || 是否显示在活动界面 |
|is_remove| smallint(1)|是 |  0 || 是否删除活动 0未删除 1删除 |
