<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_shop_goods
change exchange_score exchange_score int(11) NOT NULL DEFAULT '-1' COMMENT '所需兑换的积分,-1表示不能积分兑换，0免费，大于0表示所需积分'
html;
Db::query($sql);





echo "创建<br>\n";
