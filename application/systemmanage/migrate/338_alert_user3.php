<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users_starmaker
add detail_img varchar(500) not null default '' comment '单独详情页，顶部图片'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_users_starmaker
drop column html_info
html;
Db::query($sql);







echo "创建<br>\n";
