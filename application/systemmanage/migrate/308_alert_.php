<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_shanghu 
add register_count int NOT NULL default 0 comment '该商户已注册人数'
html;
Db::query($sql);




echo "创建<br>\n";
