<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_record_invite_starmaker 
add gold int NOT NULL default 0 comment '短视频作者为此付出的波币数，如视频认证失败会改为0'
html;
Db::query($sql);




echo "创建<br>\n";
