<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE web_article
add style tinyint not null default 1 comment '1：纯文字，2：文字1大图，3：文字3小图，4：文字小视频，5：文字大视频'
html;
Db::query($sql);

$sql=<<<html
alter TABLE web_article
add comment_count int not null default 0 comment '评论数'
html;
Db::query($sql);


$sql=<<<html
alter TABLE web_article
add source varchar(255) not null default '' comment '新闻来源'
html;
Db::query($sql);





echo "创建<br>\n";
