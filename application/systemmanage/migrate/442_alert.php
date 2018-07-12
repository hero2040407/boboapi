<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_race
add  proxy_id int not null default 0 comment '代理id'
html;
Db::query($sql);





echo "创建<br>\n";

