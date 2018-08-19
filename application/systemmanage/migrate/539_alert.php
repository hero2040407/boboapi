<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;







$sql=<<<html
alter table ds_like
add datestr int not null default 0 comment '投票日期，类似20180101'

html;
Db::query($sql);

$sql=<<<html
alter table ds_like
add index datestr(datestr)

html;
Db::query($sql);


 









echo "创建<br>\n";

