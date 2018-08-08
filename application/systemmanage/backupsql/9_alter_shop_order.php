<?php

/**
 * bb_shop_order 表
 * 加创建时间和更新时间2个字段
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_order
 drop primary key
";
Db::query($sql);

$sql="alter table bb_shop_order
drop column order_id
";
Db::query($sql);

echo "修改订单表<br>\n";

