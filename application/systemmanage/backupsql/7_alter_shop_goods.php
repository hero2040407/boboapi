<?php

/**
 * bb_shop_goods 表
 * 把几个允许空的字段改成非空
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_goods
change exchange_level exchange_level int not null default 0 comment '兑换等级'
";
Db::query($sql);


$sql="alter table bb_shop_goods
change inventory inventory int not null default 0 comment '库存'
";

Db::query($sql);

$sql="alter table bb_shop_goods
change sell_num sell_num int not null default 0 comment '销量'
";
Db::query($sql);

$sql="alter table bb_shop_goods
change pic_list pic_list varchar(1024) not null default '' comment '轮播图'
";
Db::query($sql);

$sql="alter table bb_shop_goods
change pic pic varchar(255) not null default '' comment '封面图'
";
Db::query($sql);

$sql="alter table bb_shop_goods
change model_list model_list  varchar(255) not null default '' comment '逗号分割，规格'
";
Db::query($sql);

$sql="alter table bb_shop_goods
change style_list style_list varchar(255) not null default '' comment '逗号分割，样式'
";
Db::query($sql);

$sql="alter table bb_shop_goods
change heat heat int not null default 0 comment '热度'
";
Db::query($sql);

$sql="alter table bb_shop_goods
change is_rmd is_rmd tinyint not null default 0 comment '1推荐，0未推荐'
";
Db::query($sql);

$sql="alter table bb_shop_goods
change is_remove is_remove tinyint not null default 0 comment '0未删除，1已删除'
";
Db::query($sql);

echo "修改商品表，改空为非空多个字段<br>\n";

