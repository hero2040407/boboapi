<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table ds_race
add upload_type  tinyint NOT NULL DEFAULT 0 COMMENT '1必传视频，2必传图片，3可选视频，4可选图片。'

html;
Db::query($sql);






echo "创建<br>\n";

