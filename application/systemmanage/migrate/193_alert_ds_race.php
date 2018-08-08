<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_register_log 
add has_changed tinyint not null default 0 comment '是否改变过赛区，0正常，1改变过'
html;
Db::query($sql);



echo "创建<br>\n";
