<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE web_article_comment
add like_count int not null default 0 
comment '点赞的数量，评论和回复都有的'
html;
Db::query($sql);



echo "创建<br>\n";
