<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_register_log
add race_status tinyint(4) NOT NULL DEFAULT '0' COMMENT '11线下签到，12晋级，13淘汰。'
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_register_log
add signin_time int(11) NOT NULL DEFAULT '0' COMMENT '签到时间'
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_register_log
add finish_time int(11) NOT NULL DEFAULT '0' COMMENT '最后通过时间'
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_register_log
add is_finish tinyint(4) NOT NULL DEFAULT '0' COMMENT '1最终晋级，'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_register_log
add index signin_time(signin_time)
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_register_log
add index race_status( race_status )
html;
Db::query($sql);














echo "创建<br>\n";

