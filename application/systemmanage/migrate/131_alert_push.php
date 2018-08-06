<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_push
change people people int not null default 0 comment '围观人数'
";
Db::query($sql);



echo "创建<br>\n";

