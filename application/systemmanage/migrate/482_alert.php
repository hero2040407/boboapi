<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_audition_card
add type_id int not null default 0 comment '对应bb_audition_card_type表的主键id'
html;
Db::query($sql);











echo "创建<br>\n";

