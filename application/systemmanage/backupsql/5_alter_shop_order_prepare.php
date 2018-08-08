<?php

/**
 * bb_shop_order_prepare表
 * model，style字段
 * 说明该订单中商品的规格，样式。
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_order_prepare
add model varchar(255) not null default '' comment '商品规格'        
";

Db::query($sql);

$sql="alter table bb_shop_order_prepare
add style varchar(255) not null default '' comment '商品样式'
";

Db::query($sql);

//
echo "修改临时订单表<br>\n";

