<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE web_article
add sort int not null default 0 
comment '新闻排序，从大到小'
html;
Db::query($sql);



echo "创建<br>\n";
