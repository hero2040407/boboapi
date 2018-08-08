<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add detail varchar(3000) not null default '' comment '详情，不同于简介，文字较多有图，富文本'
html;
Db::query($sql);







echo "创建<br>\n";
