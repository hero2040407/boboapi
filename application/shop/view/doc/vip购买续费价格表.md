## vip购买续费价格表

功能：返回当前的vip购买和续费价格。
~~~
/user/info/vip
~~~

| 请求参数字段        | 请求参数含义  |
| -------- |:------|
|无|  |


| 返回字段        | 类型 |含义  |
| -------- |:------|:------|
| info_list | array  | 价格列表  |
| price_list | array | 信息列表 |

| info_list| 类型 |含义  |
| -------- |:------|:------|
| no    | int    | 序号  |
| info  | string | 优惠信息 |

| price_list    | 类型 |含义  |
| -------- |:------|:------|
| type       | int  | 这是购买时要传给服务器的，默认1  |
| price      | int | 波币价格 ，如250|
| time       | string | 续费时间 ，如一个月，一年 |
| additional_info | string | 附加信息，如推荐，优惠，超值  |
| additional_yuanjia | string | 如(原价3600)  |
| second     | int   | 延长时间的秒数，客户端可以无视  |




