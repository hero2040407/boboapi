<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE bb_user_weixin_id 
add unique uid(uid)
html;
Db::query($sql);
$sql=<<<html
alter TABLE bb_user_weixin_id
add unique unionid(unionid)
html;
Db::query($sql);








echo "创建<br>\n";
