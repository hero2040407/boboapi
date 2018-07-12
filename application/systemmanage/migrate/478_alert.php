<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_question_official
add is_valid tinyint not null default 1 comment '1有效，0无效'
html;
Db::query($sql);











echo "创建<br>\n";

