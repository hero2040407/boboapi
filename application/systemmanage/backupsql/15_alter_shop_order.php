<?php

/**
 * bb_shop_order 表
 * 加第三方订单号
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_order
add third_name varchar(255) not null default '' comment '第三方支付名称'";
Db::query($sql);

$sql="alter table bb_shop_order
add third_serial varchar(255) not null default '' comment '第三方支付订单号，反查第三方用'";
Db::query($sql);



echo "修改订单表<br>\n";

