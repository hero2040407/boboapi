<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE web_article 
add is_remove tinyint not null default 0 
comment '0正常，1已删除'
html;
Db::query($sql);



echo "创建<br>\n";