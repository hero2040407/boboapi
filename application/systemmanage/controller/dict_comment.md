| 表名        | 列名 |    新的注释 |
| -------- |:------|:------|
|web_article  | type | 1最新活动，2公司动态，3媒体报道，4问题来了  |  
| web_article_comment | article_id | 此字段是复用的，对于评论来说是新闻id，对于回复来说，是评论id  |  
| web_article | title | 新闻标题  |  
| web_article |create_time  |  创建时间 |  
| web_article | id | 新闻主键  |  
| web_article | style | 1：纯文字，2：文字1大图，3：文字3小图，4：大视频，5：小视频，6两个并排小视频  |  
| backstage_admin | realname | 真实姓名，但渠道是不需要这个字段的。为空 |
| backstage_admin | phone | 手机号，但渠道是不需要这个字段的。为空 |
| backstage_auth | roles | 只能是admin,proxy,channel中的一个，不能是多个。 |
| bb_advise | pic | banner,图片大，横着放。 |
| bb_audition_card | status |   1、初始化(生成编号)；2、实体卡制作完成；3、实体卡已分发给代理；4、已销售；5、已激活,绑定uid和通告id；6、已使用；11、已注销 |
| bb_audition_card | active_time |  应该是销售时间，销售即激活 |
| bb_audition_card | serial |  卡号，只有大写字母和数字 |
| bb_audition_card | online_type |  1线上，2线下，注意(1)只会绑定线下的卡号，(2)无论线上线下，都需要预先在表中生成记录。 |
| bb_audition_card | type |  分类，是serail的前两个字符。 |
| bb_audition_card | has_pay | 1已支付，0未支付。  这个字段仅用于线上卡片，线下卡片都是0 |
| bb_audition_card_type | bigtype |   1影视，2娱乐，3专用试镜卡 | 
| bb_baoming_order | newtype |   1大赛报名，2vip申请，3通告报名 |
| bb_baoming_order_prepare | newtype |   1大赛报名，2vip申请，3通告报名 |
| bb_baoming_order | price |   价格，单位元，与字段 price_fen互斥，只有一个非零的字段是有效的 |
| bb_baoming_order | price_fen |  价格，单位分，与字段  price 互斥，只有一个非零的字段是有效的 |
| bb_baoming_order_prepare | price |   价格，单位元，与字段 price_fen互斥，只有一个非零的字段是有效的 |
| bb_baoming_order_prepare | price_fen |  价格，单位分，与字段  price 互斥，只有一个非零的字段是有效的 |
| bb_baoming_order | ds_id |  根据newtype字段区分,可能是大赛id，通告id |
| bb_baoming_order_prepare | ds_id |  根据newtype字段区分,可能是大赛id，通告id |
| bb_bottom_bar_pic | color_2x_url | 图片网址，彩色2x。注意：同一规则id下文件名绝对不可以重名，无论什么目录，而不同规则id下的文件名可以重名。下同。   |  
| bb_bottom_bar_rule | version | 后台需保证本表所有的version字段值保持一致，每修改一次任意规则的zip文件，所有行的version都加1。另外，添加删除修改规则表，也需要最后把所有行的version加1   |  
| bb_brandshop | rongyu | 荣誉，纯文字，可能有换行符  |  
| bb_brandshop | html_info | 简介，图文混排  |  
|  bb_brandshop| info |  简介，纯文字，可能有换行符 |  
| bb_brandshop_application | status |0未审核，1审核通过，2审核失败，3用户填写个人资料，4最终机构激活   |  
| bb_currency_log | type | 1波币，2怪兽蛋，10波豆，11积分  |  
|bb_config_str  | type |  1代表成就图标,2邀请分享配置,11天降红包 ,12 首页弹窗|  
|bb_toppic  | sort_id | 1代表,2代表,3代表品牌馆，11 导师栏目，12vip栏目，13机构栏目，14导师vip机构的公共轮播图  |  
| bb_toppic | linktype |  0 跳转网址, 1是学啥 , 2是邀约, 3 是玩啥， 4 大赛，11导师申请页，12vip6个成就页面，13机构申请页 |  
| bb_toppic | activity_id | 具体的活动id，如果sort_id为6则此代表品牌馆uid  |  
| bb_record_invite_starmaker | push_type | 1指定某个导师邀请，2抢单模式邀请，3官方推送  |  
|  bb_record_invite_starmaker_fail| create_time | 视频创建时间  |  
| bb_record | type |  视频类型 :1秀场 2邀约 3个人验证 4大赛,5广告，6通告上传，7动态 |  
| bb_record | usersort | 用户类型 对应usersort表id,但如果是邀约活动视频且邀约的类型是pk时，usersort=11表示红方，12表示蓝方  |  
| bb_record |time  |  视频创建时间，是时间戳 |
| bb_record |activity_id  |  type=2表示活动ID，type=4表示大赛id，type=6表示通告id，type=7表示动态id|
| bb_resource | type | 1动图，2音乐，3新版动图  |  
| bb_resource_group | type |  1动图，2音乐，3新版动图 |  
| bb_group | bb_type | 1邀约群，2大赛群，3VIP童星群（201803加）  |  
| bb_shop_order | type | 订单类型，1现金，2波币，3碎片兑换，4积分兑换  |  
| bb_starmaker_application | status |  0未审核，1审核通过，2审核失败，3用户填写个人资料，4最终导师激活 |  
| bb_task_activity | type | 活动类别 0:擂台 1:活动 2:代言招募, 3:PK  |  
| bb_toppic | picpath | 图片网址  |
| bb_toppic | title | 暂未使用  |
| bb_toppic | linkurl | 跳转地址，sort_id<20时，普通的url地址，sort_id >= 20时，孙函予的新规则  |
| bb_toppic | sort_id | 1代表,2代表,3代表品牌馆，11 导师栏目，12vip栏目，13机构栏目，14导师vip机构的公共轮播图,20 新版首页，21 星动态栏目首页顶部  |
| bb_toppic | other_browser | sort_id<20时，0内置浏览器，1跳转到其他浏览器  |
| bb_toppic | broadcast_uid | sort_id<20时，一个直播的uid  |
| bb_toppic | activity_id | sort_id<20时，具体的活动id，如果sort_id为6则此代表品牌馆uid |
| bb_toppic | linktype | sort_id<20时，0 跳转网址, 1是学啥 , 2是邀约, 3 是玩啥， 4 大赛，11导师申请页，12vip6个成就页面，13机构申请页  |
| bb_toppic | module_name | 英文表示的模块名：例如，index_top：表示首页顶部，star_top:星动态首页，brandshop:品牌馆栏目共通，audition_card:试镜卡栏目共通 |
| bb_users |email  |  谢烨201805，含义修改，原先未用，现改成最后登录ip |  
| bb_users |permissions  |  权限 1:正常用户2:管理员3:特邀用户,4 , 10 ,  11 ,  99:机器人 |  
| bb_users |vip  |  201807,此字段的新定义:1表示可以直播。0不可以。 |  
| bb_users_card | status |  1费用未支付，2正在做，3全部完成,4模卡支付费用退回且撤单 |
| bb_users_card | pic |  最终完成的模卡图片 |
| bb_users_card | pic_width |  最终完成的模卡图片宽度，单位px |
| bb_users_card | pic_height |  最终完成的模卡图片高度，单位px |
| bb_users_starmaker | detail_img | 单独详情页，顶部图片，逗号分隔  |
| bb_users_recommend | is_upgrade | 此字段未使用  |
| bb_users_updates | style | 1模卡， 2纯文字，3纯图片，4纯视频，5文字加图片，6文字加视频。  |
| bb_users_updates_like_log | type | 1动态，2动态的评论或者回复  |
| bb_vip_application_log | status |  0未审核，1认证费50元已成功缴纳，2已经填写申请资料，3已经完善了个人资料，4导师面试通过，5导师面试不合格，6最终管理员审核通过，7、非常特殊，用户点击完善资料后生成，可能是面试通过，也可能是当时用户已满足6个条件。<br> |  
| bb_vip_application_log | admin_name | status=4or5时是导师名称,或stats=6时是管理员名称  |  
| bb_vip_application_log | admin_time |  status=4or5时是导师审核时间,或stats=6时是管理员审核时间 |  
| bb_record_invite_starmaker | new_status | 1、发起群发邀请，星推官字段为0<br>2、发起单个的邀请，已经指定了一个人。或者群发邀请，某一导师抢单成功。状态2还未点评内容。星推官字段有值了。<br>3、导师点评了。但未审核。内容字段有值了。<br>4、审核成功。<br>5、审核失败-转群发邀请。审核失败后，按产品说法，应该回到最初的状态。<br>6、审核失败-转单个邀请。审核失败后，按产品说法，应该回到最初的状态。<br>  |  
| ds_dangan_config | type |  1单行文本，2多行文本，3复选，4单选，5下拉，6上传图片，7城市选择  |
| ds_race_field | status |  0等待中，1报名，2比赛，3结束  |  
| ds_race_message | target_type | 限制条件：  0全部， 1成功者，2失败者  | 
| ds_race_message | field_id |  赛区id，可以为0，表示给大赛所有人发送消息  |
| ds_race_message | is_valid |  0待审核，1审核过立刻发送，2审核失败  |
| ds_register_log | ds_id |  <font color=red>注意，是赛区id</font>  |  
| ds_register_log | qudao_id |  该字段废止。xieye2018 05  |  
| ds_register_log | is_finish |  1最终晋级，2最终淘汰  |  
| ds_register_log | upload_checked |  0:上传图片或视频未审核，1已审核,2审核未通过  |  
| ds_register_log | has_upload |  0:未上传，1已上传（无论是否审核过）  |  


