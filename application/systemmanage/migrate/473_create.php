<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;







$sql=<<<html
alter TABLE bb_user_activity 
add paiming_new int not null default 0 comment '新排名，201806，从1开始'

 
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_user_activity
add index paiming_new(paiming_new)

html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_user_activity
add zan int not null default 0 comment '统计排名时的赞数'


html;
Db::query($sql);













echo "创建<br>\n";

