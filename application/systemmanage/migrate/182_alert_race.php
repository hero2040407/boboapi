<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add index parent(parent)
html;
Db::query($sql);







echo "创建<br>\n";
