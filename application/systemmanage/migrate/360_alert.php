<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE web_article_comment
add reply_count int not null default 0 
comment '回复的数量，只针对评论而言，对回复无意义'
html;
Db::query($sql);



echo "创建<br>\n";
