
## 用户VIP条件展示

功能：

1. 用户打算申请vip时，该接口可以返回自动认证需要的一些用户状况，包括用户等级达成百分比，成就百分比，审核视频百分比，关注用户百分比，粉丝数，活动次数。

vip申请进度：
~~~
 假如我已经是童星或机构或导师，则status=0，不显示按钮。
      
 假如不是童星
 
        假设用户已有其他角色申请，这里直接不给用户操作，按钮变灰：status=8
 
         假如6个条件满足，
               假如未更新过个人资料，则“完善资料，更新个人主页”， 点击跳转到个人主页， status=1，
               假如已更新过资料，则“请等待审核完成”，不能点击，status=2，
        假如6条件没有都满足
              假如我连手机号都填过了，   
                        假如未审核， 则“请等待审核完成”，不能点击，status=3，
                       假如审核不通过，则不显示按钮，status=6
                       假如审核通过，则“完善资料，更新个人主页”， 点击跳转到个人主页， status=7，
              假如手机号未填过
                     假如钱付过了，则“连线导师进入快速认证通道”，点击跳转到手机号填写页面，status = 4，
                    假如钱没付过，则“连线导师进入快速认证通道”，点击跳转到显示价格50元页面。status= 5，
~~~


~~~
/user/vip/index
~~~
~~~
GET
~~~

| 请求参数字段        | 请求参数含义  |
| -------- |:------|
|uid|用户id|



| 返回字段        | 类型 |含义  |
| -------- |:------|:------|
| list     | array | 每个元素是一个对象，见下，总共6个元素固定。 |
| money     | int | 用户付钱申请vip，所需要支付的现金，单位元。 |
| complete     | bool | 为真表示6个条件全部达成。 |
| status     |int | 申请VIP进度，见上方进度说明 |
| status_word     | string | 按钮文字，如这个字段为空字符串，则不显示按钮。 |
| group     | object | vip童星qq群，请一定注意可能为null，见下 |

| group        | 类型 |含义  |
| -------- |:------|:------|
| pic   | string |  群图标  |
| qrcode_pic   | string |  二维码图标  |
| title   | string |  群名称  |
| summary   | string |  群简介  |
| code   | string |  qq号  |
|  group_or_person  | int | 1qq群号，2qq个人号  |


| list每行        | 类型 |含义  |
| -------- |:------|:------|
| number     | int | 实质是一个百分比，100表示100%,60表示60%，如果100表示该条件达成 |
| word     | string | 文字表示的条件，例如：等级达到10级 |


