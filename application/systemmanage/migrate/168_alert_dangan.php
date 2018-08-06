<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_dangan 
add type tinyint  not null default 0 comment '同ds_dangan_config表，1复选框，2文本框，3上传，4简介' 
html;
Db::query($sql);









echo "创建<br>\n";
