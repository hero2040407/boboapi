<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_toppic
add  broadcast_uid  int not null default 0 comment '一个直播的uid'
html;
Db::query($sql);




echo "创建<br>\n";
