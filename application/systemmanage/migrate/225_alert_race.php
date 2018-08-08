<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_question
add answer_uid int  not null default 10000 comment '回答者的uid，需后台设置'    
html;
Db::query($sql);





echo "创建<br>\n";
