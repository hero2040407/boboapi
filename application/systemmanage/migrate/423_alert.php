<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_register_log
add  is_web_baoming  tinyint not null default 0 comment '1表示vue的web页面报名，0表示app内报名' 
html;
Db::query($sql);






















echo "创建<br>\n";

