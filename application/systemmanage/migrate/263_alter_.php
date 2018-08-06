<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE bb_baoming_order_prepare
add serial varchar(100) not null default '' comment '怪兽订单号，BM开头'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_baoming_order_prepare
add index serial (serial)
html;
Db::query($sql);











echo "创建<br>\n";
