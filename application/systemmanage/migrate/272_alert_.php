<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_group 
add  summary varchar(500)  NOT NULL DEFAULT '' COMMENT '群简介'
html;
Db::query($sql);




echo "创建<br>\n";
