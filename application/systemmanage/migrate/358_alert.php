<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE web_article_comment
add is_reply tinyint not null default 0 
comment '0评论，1对评论的回复'
html;
Db::query($sql);



echo "创建<br>\n";
