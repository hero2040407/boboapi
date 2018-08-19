<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;







$sql=<<<html
alter table ds_register_log
add has_upload tinyint not null default 0 comment '1已上传，或大赛可以不上传，0必须上传时未上传'

html;
Db::query($sql);









echo "创建<br>\n";

