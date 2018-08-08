<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_question
change question question varchar(1000)  not null default '' comment '提问'    
html;
Db::query($sql);







echo "创建<br>\n";
