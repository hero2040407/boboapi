<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_brandshop 
add sort int NOT NULL  DEFAULT 0 COMMENT '前端排序，大的靠前'
html;
Db::query($sql);



echo "创建<br>\n";

