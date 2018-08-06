<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_dashang_log
add bean int not  null default 0 comment '收到 的波豆'
";
Db::query($sql);



echo "创建<br>\n";

