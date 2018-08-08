<?php

/**
 * bb_shop_order_prepare 表
 * 加终端类型字段，手机型号收集
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_order_prepare
add terminal varchar(255) not null default '' comment '终端型号'";
Db::query($sql);

echo "修改临时订单表<br>\n";

