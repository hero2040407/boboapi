<?php

/**
 * 修改bb_record表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_record
change `like` `like` int not null default 0  comment '视频点赞数'
html;
Db::query($sql);




echo "修改bb_record表<br>\n";

