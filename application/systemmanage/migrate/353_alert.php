<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter table web_article
add  pic_count int not null default 0 comment '该新闻所有图片的数量'
html;
Db::query($sql);



echo "创建<br>\n";
