<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_activity_comments_reply
add index     comments_id(comments_id)    
";
Db::query($sql);

$sql="alter table bb_activity_comments_reply
add index     uid(uid)
";
Db::query($sql);

$sql="alter table bb_rewind_comments_reply
add index     comments_id(comments_id)
";
Db::query($sql);

$sql="alter table bb_rewind_comments_reply
add index     uid(uid)
";
Db::query($sql);


$sql="alter table bb_task_comments_reply
add index     comments_id(comments_id)
";
Db::query($sql);

$sql="alter table bb_task_comments_reply
add index     uid(uid)
";
Db::query($sql);




//
echo "修改bbcy表<br>\n";

