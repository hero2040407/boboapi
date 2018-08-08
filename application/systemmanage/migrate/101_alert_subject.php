<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_subject_movie
add  is_recommend tinyint  not null default 0 comment '1推荐首页显示，0首页不显示'
";
Db::query($sql);




echo "创建<br>\n";
