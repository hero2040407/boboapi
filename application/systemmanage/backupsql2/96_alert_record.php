<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_record
add  subject_title  varchar(255)  not null default '' comment '栏目标题'
";
Db::query($sql);

$sql="alter table bb_record
add subject_pic varchar(255) not null default '' comment '栏目图片'
";
Db::query($sql);

$sql="alter table bb_record
add subject_sort int not null default 0 comment '栏目序号，从小到大'
";
Db::query($sql);


echo "创建<br>\n";
