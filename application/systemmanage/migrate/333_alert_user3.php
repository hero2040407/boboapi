<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_record_like
add datestr int not null default 0 comment '1被展示，0被隐藏，正常是1'        
html;
Db::query($sql);












echo "创建<br>\n";
