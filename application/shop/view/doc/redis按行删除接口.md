
## redis按行删除

功能：redis按行删除，主要给后台调用
~~~
/api/redis/remove
~~~

| 请求参数字段        | 请求参数含义  |
| -------- |:------|
|type| 类型 ，见说明|
|id| id ，见说明 |

| type含义        | id含义  |
| -------- |:------|
| activity | 删除 bb_activity表的id行 |
|config| 删除config，id可能是其中一个['bb_config', 具体请看陈岳代码， 'bb_role', 'bb_usersort', 'bb_label', 'bb_label_activity', 'bb_label_learn', |
| record | 删除 id 是短视频的room_id |
| push | id 是 用户id，即uid |
| user | id 是 用户id，即uid |
| bb_task_user | id 是用户id，删除该用户的所有任务。bb_task_user表 |
| bb_task_activity | id 是活动id，。bb_task_activity表的id行 |
| bb_task | id 是bb_task表的id行 |
| bb_monster_animation | id 是 bb_monster_animation表 的id |
| bb_monster_list | id 是 bb_monster_list表 的id |
| comments | id是表名+ comments_id + type |



| 返回字段        | 类型 |含义  |
| -------- |:------|:------|
|无        |      | |


