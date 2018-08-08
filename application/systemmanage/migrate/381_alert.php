<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_record_invite_starmaker_log
add is_houtai_dianping tinyint  not null default 0 comment '0普通，1后台帮导师点评'
html;
Db::query($sql);







echo "创建<br>\n";

