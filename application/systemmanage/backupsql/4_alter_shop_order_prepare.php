<?php

/**
 * bb_shop_order_prepare表
 * count字段
 * 说明该订单中有多少个商品。
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_order_prepare
add count  int not null default 0 comment '商品数量'        
";

Db::query($sql);

//
echo "修改临时订单表<br>\n";

