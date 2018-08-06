<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_face
add pic_gray varchar(255) NOT NULL DEFAULT '' COMMENT '灰度图，目前只用于组图标'
html;
Db::query($sql);













echo "创建<br>\n";

