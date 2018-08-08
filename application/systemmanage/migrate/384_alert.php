<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_tongji_huizong
add liucun30 decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '30日留存'
html;
Db::query($sql);










echo "创建<br>\n";

