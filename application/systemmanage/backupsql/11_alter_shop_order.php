<?php

/**
 * bb_shop_order 表
 * 加主键id
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_order
add price decimal(10,2) not null default 0 comment '订单总金额' ";
Db::query($sql);

$sql="alter table bb_shop_order
add type tinyint  not null default 0 comment '1现金，2波币' ";
Db::query($sql);

$sql="alter table bb_shop_order
add goods_id  int  not null default 0 comment '商品id' ";
Db::query($sql);

$sql="alter table bb_shop_order
add serial varchar(100)   not null default '' comment '订单号' ";
Db::query($sql);

$sql="alter table bb_shop_order
add is_success tinyint    not null default 0 comment '0待定，1成功付款' ";
Db::query($sql);

$sql="alter table bb_shop_order
add terminal  varchar(255)   not null default '' comment '终端型号' ";
Db::query($sql);

$sql="alter table bb_shop_order
add create_time int   not null default 0 comment '生成时间' ";
Db::query($sql);

$sql="alter table bb_shop_order
add update_time int   not null default 0 comment '更新时间' ";
Db::query($sql);

















echo "修改订单表<br>\n";

