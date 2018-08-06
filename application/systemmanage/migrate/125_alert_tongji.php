<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_tongji_huizong
add share_count int  not null default 0 comment '分享数量'
";
Db::query($sql);

echo "创建<br>\n";

