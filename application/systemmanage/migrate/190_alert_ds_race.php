<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_dangan 
drop column zong_ds_id
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_dangan_config
drop column zong_ds_id
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_show_video
drop column zong_ds_id
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_record
drop column zong_ds_id
html;
Db::query($sql);




echo "创建<br>\n";
