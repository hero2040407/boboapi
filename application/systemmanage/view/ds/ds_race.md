### bb_race表
  
| 字段        | 类型 |    注释 |
| -------- |:------|:------|
|id| int(11) |   主键, 自增, |  
|title| varchar |   大赛名称 |  
|area| varchar |   地区名，即分赛场名称 |  
|level| int(11) |  1主赛场，2分赛场 |  
|banner| varchar |   banner |  
|uid| int(11) |   主办人id |  
|summary| varchar |   简介，不要带图片啊。只是文字。也不是html文本。 |  
|create_time| int(11) |   创建时间 |  
|start_time| int(11) |  大赛开始时间|  
|end_time| int(11) |   大赛结束时间 |  
|register_start_time| int(11) |   报名起始时间 |  
|register_end_time| int(11) |  报名结束时间 |  
|money| float |   人民币费用，单位元 |  
|reward| varchar |   大赛奖励 |  
|address| varchar |   非常详细的地址，到多少路多少号 |  
|is_active| int(11) |   1有效，0无效 |  
|scope_level| int(11) |   1不限制报名，2只能后台报名 |  
|has_dangan| int(11) |   1有档案，0无档案 |  
|parent| int(11) |   主赛场id，如果自身是主赛场，则为0 |  
|area_id| int |   分赛区地区id，对应bb_area表的主键 |  
|area1_name| varchar |  省的名称，没有就不填 |  
|area2_name| varchar |   市的名称，没有就不填 |  
|area3_name| varchar |   区的名称，没有就不填 |  
|has_pic| int |   0没有个人照片，1需要个人照片。 |  
|detail| varchar |  富文本。 大赛详情，文字多带图片 |  

