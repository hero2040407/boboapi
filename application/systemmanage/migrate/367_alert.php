<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter TABLE bb_record_invite_starmaker
add new_status tinyint not null default 0
comment '新状态，参加数据字典'
html;
Db::query($sql);



echo "创建<br>\n";
