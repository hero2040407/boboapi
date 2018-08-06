<?php
use think\Db;
$sql="alter table bb_alitemp add test1 int not null default 0 comment '测试字段'";

Db::query($sql);

//xieye注：请勿直接打印sql语句，防止不相关的人看到。
echo "修改了alitemp";

