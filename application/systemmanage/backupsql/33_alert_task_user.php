<?php

/**
 * 修改用户任务表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_task_user
add unique uid(uid)
html;
Db::query($sql);




echo "修改用户任务表<br>\n";

