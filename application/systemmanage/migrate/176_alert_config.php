<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_dangan_config
add upload_type tinyint not null default 1  comment '1普通上传，2大图片框上传'
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_dangan_config
add info varchar(255) not null default ''  comment '其他描述'
html;
Db::query($sql);






echo "创建<br>\n";
