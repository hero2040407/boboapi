<?php

/**
 * bb_shop_order 表
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_order
add count int not null default 0 comment '商品数量' ";
Db::query($sql);

$sql="alter table bb_shop_order
add model varchar(255)  not null default '' comment '规格' ";
Db::query($sql);

$sql="alter table bb_shop_order
add style varchar(255)  not null default '' comment '样式' ";
Db::query($sql);



echo "修改订单表<br>\n";

