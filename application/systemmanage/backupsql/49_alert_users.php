<?php

/**
 * 修改bb_users表
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter table bb_users
add not_zhibo tinyint  not null default 0 comment '0正常，1禁止直播'
html;
Db::query($sql);


$sql=<<<html
alter table bb_users
add not_fayan tinyint  not null default 0 comment '0正常，1禁止发言'
html;
Db::query($sql);




echo "修改bb_users表<br>\n";
