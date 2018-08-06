<?php

/**
 * 修改bb_shop_goods表，加索引
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_shop_goods
add show_pic_list varchar(500) not null default '' comment '展示图的json'
html;
Db::query($sql);







echo "修改bb_shop_goods表<br>\n";

