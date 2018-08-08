<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_question
drop column     qusetion_uid    
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_question
add  question_uid int not null default 0 comment '提问者uid'
html;
Db::query($sql);




echo "创建<br>\n";
