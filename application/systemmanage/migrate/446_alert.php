<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_race 
add online_type tinyint not null default 1 comment '大赛类型  1 纯线上，2线下'
html;
Db::query($sql);




echo "创建<br>\n";

