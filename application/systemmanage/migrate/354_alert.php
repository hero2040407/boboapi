<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter table web_article
add  status tinyint not null default 0 comment '0未审核，1已审核'
html;
Db::query($sql);



echo "创建<br>\n";
