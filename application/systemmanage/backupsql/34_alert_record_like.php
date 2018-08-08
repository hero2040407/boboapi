<?php

/**
 * 修改bb_record_like表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_record_like
add ip varchar(255) not null default '' comment 'html页面点赞，用ip字段判断是否重复'
html;
Db::query($sql);




echo "修改bb_record_like表<br>\n";

