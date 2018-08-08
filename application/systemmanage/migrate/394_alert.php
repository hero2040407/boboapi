<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_baoming_order
add newtype tinyint NOT NULL DEFAULT 1 COMMENT '1大赛报名，2vip申请'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_baoming_order_prepare
add newtype tinyint NOT NULL DEFAULT 1 COMMENT '1大赛报名，2vip申请'
html;
Db::query($sql);














echo "创建<br>\n";

