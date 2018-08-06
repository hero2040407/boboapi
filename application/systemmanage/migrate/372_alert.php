<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_request 
add duration int NOT NULL  DEFAULT 0 COMMENT '程序用时'
html;
Db::query($sql);



echo "创建<br>\n";

