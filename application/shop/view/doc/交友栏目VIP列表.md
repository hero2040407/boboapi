
## 交友栏目，加V推荐列表

功能：

1. 显示vip列表，但都是我没有关注的，是分页的。

~~~
/user/friendinside/vip_index
~~~
~~~
GET
~~~

| 请求参数字段        | 请求参数含义  |
| -------- |:------|
|uid|用户id|
|startid|起始序号|
|length|长度|

| 返回字段        | 类型 |含义  |
| -------- |:------|:------|
| list     | array | vip列表，见下 |
| is_bottom     | int | 1到底 |


| list每行        | 类型 |含义  |
| -------- |:------|:------|
| uid     | int | uid |
| pic     | string | 头像 |
| level     | int | 级别 |
| nickname     | string | 昵称 |
| signature     | string | 一句话签名，可能空字符串 |
| fans_count     | int | 粉丝数 |
|follow_count     | int | 关注数量 |
|sex     | int | 1男 0女 |






