<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_face 
add title varchar(255) NOT NULL DEFAULT '' COMMENT '说明文字'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_face
add link varchar(255) NOT NULL DEFAULT '' COMMENT '真正的资源文件路径，pic字段只是展示图片。'
html;
Db::query($sql);










echo "创建<br>\n";

