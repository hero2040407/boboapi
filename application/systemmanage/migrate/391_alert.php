<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_dashang_log 
add currency_log_id int NOT NULL DEFAULT 0 COMMENT '关联到bb_currency_log表的id'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_dashang_log
add index currency_log_id(currency_log_id)
html;
Db::query($sql);












echo "创建<br>\n";

