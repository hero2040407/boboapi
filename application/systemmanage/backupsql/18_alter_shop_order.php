<?php

/**
 * bb_shop_order 表
 * 修改订单表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_shop_order
add logistics_is_order tinyint not null default 0 comment '是否给物流公司下单成功' 
html;


Db::query($sql);


$sql=<<<html
alter table bb_shop_order
add logistics_is_pickup tinyint not null default 0 comment '物流公司是否上门取件'
html;

Db::query($sql);


$sql=<<<html
alter table bb_shop_order
add logistics_is_complete tinyint not null default 0 
   comment '用户是否签收'
html;

Db::query($sql);



echo "修改订单表<br>\n";

