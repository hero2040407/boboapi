<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_tongji_huizong
add money_yaoqing int NOT NULL DEFAULT '0' COMMENT '邀请导师鉴定消耗BO币'
html;
Db::query($sql);










echo "创建<br>\n";

