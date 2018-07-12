### bb_shop_goods表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id| int(11) unsigned| |   |主键, 自增, | 商品id |
|exchange_level| int(11)| |  0 || 兑换等级 |
|currency| int(11)| |  -1 || 商品虚拟货币 -1为不允许虚拟货币购买 0为免费 其他正数表示所需货币数量 |
|money| float| |  0 || 人民币所需金额-1为不允许人民币购买 0为免费 其他正数表示所需货币数量 |
|discount| int(2)| |  10 || 打折比率 10为不打折 取值范围1-10 |
|name| varchar(50)|是 |   || 商品名称 |
|title| varchar(255)| |   || 商品的标题 |
|info| varchar(1024)| |   || 商品的描述信息 |
|inventory| int(11)| |  0 || 库存 |
|sell_num| int(11)| |  0 || 销量 |
|pic_list| varchar(1024)| |   || 轮播图 |
|pic| varchar(255)| |   || 封面图 |
|model_list| varchar(255)| |   || 逗号分割，规格 |
|style_list| varchar(255)| |   || 逗号分割，样式 |
|heat| int(11)| |  0 || 热度 |
|is_rmd| tinyint(4)| |  0 || 1推荐，0未推荐 |
|label| int(4)|是 |  0 || 标签 |
|is_remove| tinyint(4)| |  0 || 0未删除，1已删除 |
|real_discount| int(11)| |  10 || 真实折扣1－10，已考虑了促销期间，系统自动更新 |
|on_sale_start_time| int(11)| |  0 || 促销起始时间，时间戳 |
|on_sale_end_time| int(11)| |  0 || 促销结束时间，时间戳 |
|create_time| int(11)| |  0 || 创建时间 |
|update_time| int(11)| |  0 || 最后修改时间 |
|show_pic_list| varchar(255)| |   || 展示图列表json |
