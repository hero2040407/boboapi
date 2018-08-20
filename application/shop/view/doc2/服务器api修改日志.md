## 服务器api修改日志

进入：[全部 api 检索](/systemmanage/tool/api)

| 大版本        | 内容  |
| :--------: |:------|
|5| 大赛大改，时间是2018-08 |
|2.1| 小改版，时间是2018-04 |
|2| 产品大改版，时间是2018-04 |
 

| 版本        | 标签  | 修改日志  | 影响接口  | 日期  |
| :--------: |:------|:------|:------|:------|
| 5 |U  | 大赛单个详情| [/race/index_v2/ds_one_new](/shop/doc/index2/name/大赛单个详情新) | 2018-08-19 |
| 5 |U  | 大赛人气榜单 | [/race/share/rank](/shop/doc/index2/name/大赛人气榜单) | 2018-08-20 |
| 5 |U  | 大赛个人分享页面| [/api/share/race](/shop/doc/index2/name/大赛个人分享页面) | 2018-08-19 |
| 5 |U  | 大赛个人分享投票| [/api/share/like](/shop/doc/index2/name/大赛个人分享页面) | 2018-08-19 |
| 5 |U  | 大赛报名| [/race/weblogin/register_new](/shop/doc/index2/name/大赛微信报名) | 2018-08-17 |
| 5 |U  | 大赛报名状态| [/race/index/get_user_status_new](/shop/doc/index2/name/大赛报名状态新) | 2018-08-17 |
| 5 |U  | 大赛赛区选择和个人信息| [/race/index_v2/select_field](/shop/doc/index2/name/大赛赛区选择和个人信息) | 2018-08-17 |
| 3.0 |+  | 新版用户消息推送配置，设置 | [/api/pushconfig/set_config](/shop/doc/index2/name/新版用户消息推送配置) | 2018-06-26 |
| 3.0 |+  | 新版用户消息推送配置 ，获取| [/api/pushconfig/get_config](/shop/doc/index2/name/新版用户消息推送配置) | 2018-06-26 |
| 2.1 |+  | 动图接口 | [/api/resource/gif](/shop/doc/index2/name/资源接口) | 2018-04-27 |
| 2.1 |U  | 回播评论，绑定手机校验 | /push/comments/comments | 2018-05-07 |
| 2.1 |U  | 短视频评论，绑定手机校验 | /record/comments/comments | 2018-05-07 |
| 2.1 |U  | 短视频回复，绑定手机校验 | /record/comments/reply | 2018-05-07 |
| 2.1 |U  | 回播回复，绑定手机校验 | /push/comments/reply | 2018-05-07 |
| 2.1 |U  | 提现申请，绑定手机校验 | [/shop/tixian/index](/shop/doc/index2/name/提现接口) | 2018-05-07 |
| 2.1 |U  | 打赏，绑定手机校验 | [/shop/dashangbean/index](/shop/doc/index/name/打赏视频接口_波豆) | 2018-05-07 |
| 2.1 |U  | 打赏，ip校验 | [/shop/dashangbean/index](/shop/doc/index/name/打赏视频接口_波豆) | 2018-05-07 |
| 2.1 |U  | 大赛列表，加主办方昵称 | [/race/index_v2/ds_list](/shop/doc/index2/name/大赛首页大赛列表) | 2018-05-08 |
| 2.1 |U  | 大赛详情，加主办方昵称 | [/race/index_v2/ds_one](/shop/doc/index2/name/大赛单个详情) | 2018-05-08 |
| 2.1 |+  | 首页弹框 | [/api/boboapi/index_window](/shop/doc/index2/name/首页弹框) | 2018-05-08 |
| 2.1 |U  | 邀约单个活动信息h5 | [/task/taskactivityapi/h5_detail](/shop/doc/index2/name/邀约单个活动信息h5) | 2018-05-09 |
| 2.1 |U  | 大赛单个详情h5 | [/race/index/h5_detail](/shop/doc/index2/name/大赛单个详情h5) | 2018-05-09 |
| 2.1 |U  | 注册接口，注册时检查昵称是否有敏感词，过滤 | [/user/login/index](/shop/doc/index/name/用户注册和登录2018) | 2018-05-10 |
| 2.1 |+  | app底部导航接口，安卓版 | [/api/boboapi/bottom_bar_for_android](/shop/doc/index2/name/底部导航图标) | 2018-05-11 |
| 2.1 |+  | app底部导航接口，ios版 | [/api/boboapi/bottom_bar_for_ios](/shop/doc/index2/name/底部导航图标) | 2018-05-11 |
| 2.1 |U  | 个人详情，加一个speciality_arr字段，方便安卓 | /user/user/get_userallinfo | 2018-05-14 |
| 2.1 |U  | 人脸贴图，加我的，最新，最热  | [/api/face/index](/shop/doc/index2/name/人脸贴图) | 2018-05-15 |
| 2.1 |U  | 新版gif动图，加我的，最新，最热  | [/api/resource/gif](/shop/doc/index2/name/资源接口) | 2018-05-15 |
| 2.1 |+  | 资源使用记录接口  | [/api/face/download](/shop/doc/index2/name/资源接口) | 2018-05-15 |
| 2.1 |+  | 大赛报名状态v2  | [/race/index/get_user_status_v2](/shop/doc/index2/name/大赛报名状态) | 2018-05-16 |
| 2.1 |U  | 个人详细信息，增加可选的token验证  | [/user/user/get_userallinfo](/shop/doc/index/name/用户详细信息) | 2018-05-29 |
| 2.1 |+  | 大赛报名状态新版  | [/race/index/get_user_status_new](/shop/doc/index2/name/大赛报名状态新) | 2018-05-31 |
| 2.1 |+  | 大赛首页大赛列表 ，新版，改状态，  | [/race/index_v2/ds_list_new](/shop/doc/index2/name/大赛首页大赛列表新) | 2018-05-31 |
| 2.1 |+  | 大赛详情新版 ，改状态，加动态列表 | [/race/index_v2/ds_one_new](/shop/doc/index2/name/大赛单个详情新) | 2018-05-31 |
| 2.1 |+  | 品牌馆通告分页，状态文字修改 | [/show/brandshop/schedule_list_v2](/shop/doc/index/name/品牌馆通告分页新) | 2018-06-01 |
| 2.1 |U  | 活动列表，加describe描述参赛状态字段 | [/task/taskactivityapi_v2/newlist](/shop/doc/index2/name/邀约活动列表201704) | 2018-06-12 |
| 2.1 |+  | 邀约的视频列表，统一加排名字段， | [/task/taskactivityapi_v2/get_user_list](/shop/doc/index2/name/邀约活动内视频列表) | 2018-06-12 |
| 2.0 | + | 音乐资源接口，供用户上传视频时选择，做为背景音乐 | [/api/resource/mp3](/shop/doc/index2/name/资源接口) | 2018-04-19 |
| 2.0 |+  | 手机验证接口，可以单独调用 | [/user/login/check_phone_code](/shop/doc/index/name/用户手机号验证) | 2018-04-19 |
| 2.0 |U  | 话题接口，加uid参数 | [/record/theme/index](/shop/doc/index/name/视频话题接口) | 2018-04-20 |












<!--
<table>
<thead>

<tr>
  <th width='10%'  align="center">版本</th>
  <th  width='10%' align="center">标签</th>
  <th  width='50%' align="center">修改日志</th>
  <th  width='20%' align="center">影响接口</th>
  <th  width='10%' align="center">修改日期</th>
</tr>
</thead>
<tbody>

<tr>
  <td   style="vertical-align:middle"  align="center">2.0</td>
  <td align="left"></td>
  <td align="left">修改内容1</td>
  <td align="left"></td>
  <td align="left">2018-04-19</td>
</tr>

<tr>
  <td   style="vertical-align:middle"  align="center">2.0</td>
  <td align="left"></td>
  <td align="left">修改内容2</td>
  <td align="left"></td>
  <td align="left">2018-04-20</td>
</tr>




</tbody>
</table>
-->
