<?php

/**
 * bb_shop_order 表
 * 加主键id
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_order
add id int not null primary key auto_increment;
";
Db::query($sql);



echo "修改订单表<br>\n";

