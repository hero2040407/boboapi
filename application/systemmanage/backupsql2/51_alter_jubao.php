<?php

/**
 * bb_jubao_log表
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_jubao_log
add content varchar(3000) not null default '' comment '举报内容'        
";

Db::query($sql);



//
echo "修改bb_jubao_log表<br>\n";

