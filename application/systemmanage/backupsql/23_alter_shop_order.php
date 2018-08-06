<?php

/**
 * bb_shop_order
 * 修改订单表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_shop_order
add is_user_delete tinyint  NOT NULL DEFAULT 0 COMMENT '是否用户删除'
html;
Db::query($sql);


echo "修改订单表<br>\n";

