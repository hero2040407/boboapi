<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter table ds_register_log
add upload_checked tinyint not null default 0 comment '0:上传图片或视频未审核，1已审核'

html;
Db::query($sql);



 



echo "创建<br>\n";

