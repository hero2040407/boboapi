<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_label
add  hot_days int not null default 0 comment '热门持续天数的限制，到期自动转非热门'        
html;
Db::query($sql);




echo "创建<br>\n";
