<?php

/**
 * 修改bb_focus表，加索引
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_focus
add index uid(uid)
html;
Db::query($sql);


$sql=<<<html
alter table bb_focus
add index focus_uid(focus_uid)
html;
Db::query($sql);



echo "修改bb_focus表<br>\n";

