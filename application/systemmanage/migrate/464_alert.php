<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_race
add repeat_status tinyint(4) NOT NULL DEFAULT '1' COMMENT '1允许重复报名，2只能报名一次。'
html;
Db::query($sql);














echo "创建<br>\n";

