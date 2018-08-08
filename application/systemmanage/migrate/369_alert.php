<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_record_invite_starmaker_log 
add reason varchar(1000) NOT NULL  DEFAULT '' COMMENT '审核失败理由'
html;
Db::query($sql);



echo "创建<br>\n";

