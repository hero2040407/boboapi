<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter table ds_register_log
add record_url varchar(255) NOT NULL DEFAULT '' COMMENT '短视频url'

html;
Db::query($sql);


$sql=<<<html
alter table ds_register_log
add record_duration int NOT NULL DEFAULT 0 COMMENT '短视频时长，单位秒'

html;
Db::query($sql);

$sql=<<<html
alter table ds_register_log
add record_cover varchar(255) NOT NULL DEFAULT '' COMMENT '短视频封面'

html;
Db::query($sql);








echo "创建<br>\n";

