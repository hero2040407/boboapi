<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE web_article_comment
add reply_time int not null default 0 
comment '对于该评论的回复的最新时间，对于回复此字段无意义'
html;
Db::query($sql);



echo "创建<br>\n";
