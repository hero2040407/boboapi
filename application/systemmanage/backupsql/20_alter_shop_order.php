<?php

/**
 * bb_shop_order 表 外加 临时订单表
 * 修改订单表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_shop_order
add terminal_type tinyint not null default 0 comment '1ios，2安卓' 
html;


Db::query($sql);


$sql=<<<html
alter table bb_shop_order_prepare
add terminal_type tinyint not null default 0 comment '1ios，2安卓' 
html;

Db::query($sql);





$sql=<<<html
alter table bb_shop_order
drop column  mobile_type 
html;

Db::query($sql);

$sql=<<<html
alter table bb_shop_order_prepare
drop column  mobile_type 
html;

Db::query($sql);






echo "修改订单表和临时订单表<br>\n";

