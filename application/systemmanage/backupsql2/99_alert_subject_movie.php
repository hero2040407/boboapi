<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_subject_movie
add  sort  int  not null default 0 comment '排序'
";
Db::query($sql);




echo "创建<br>\n";
