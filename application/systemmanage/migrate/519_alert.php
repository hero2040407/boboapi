<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_advise
add reward varchar(255) not null default '' comment '简短文字描述，通告的奖励是多少，显示在列表中'
html;
Db::query($sql);























echo "创建<br>\n";

