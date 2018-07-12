
## 用户VIP随机获取

功能：修改vip资料，或申请vip时，页面上有随机显示某个vip童星的资料。结果会排除掉传入参数的uid，也排除掉经验和个性为空的童星。
功能：如果一个都没有查到，会返回code=0.

~~~
/user/info/random_vip
~~~

| 请求参数字段        | 请求参数含义  |
| -------- |:------|
|uid|  用户id|

| 返回字段        | 类型 |含义  |
| -------- |:------|:------|
| uid     | int | 查找到的uid |
| nickname     | string | 昵称 |
| pic     | string | 头像 |
| jingyan     | string | 经验，竖线分割 |
| gexing     | string | 个性，竖线分割 |




返回：随机返回一个vip的uid，会排除参数。

