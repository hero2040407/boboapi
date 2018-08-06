<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_record
add index video_path(video_path)
";
Db::query($sql);






echo "创建<br>\n";
