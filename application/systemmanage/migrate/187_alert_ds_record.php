<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_record 
add zong_ds_id int not null default 0 comment '总赛事id，一定是level为1的大赛id，对于ds_race表'
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_record
add index zong_ds_id(zong_ds_id)
html;
Db::query($sql);




echo "创建<br>\n";
