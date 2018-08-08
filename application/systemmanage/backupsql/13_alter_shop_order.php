<?php

/**
 * bb_shop_order_prepare 表
 * 提高精度，应对测试时用的分
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_order_prepare
change price price decimal(10,2) not null default 0 comment '总价' ";
Db::query($sql);





echo "修改临时订单表<br>\n";

