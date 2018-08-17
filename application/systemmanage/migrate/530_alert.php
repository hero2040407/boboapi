<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table ds_register_log
add pic_id_list  varchar(255) NOT NULL DEFAULT '' COMMENT '用户上传的多张图片，冗余字段，对应bb_pic表的id，用逗号分隔，例如1,2'

html;
Db::query($sql);






echo "创建<br>\n";

