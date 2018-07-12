<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE backstage_admin
add index pwd(pwd)
html;
Db::query($sql);

$sql=<<<html
alter TABLE backstage_admin
add index realname(realname)
html;
Db::query($sql);

$sql=<<<html
alter TABLE backstage_admin
add index phone(phone)
html;
Db::query($sql);

$sql=<<<html
alter TABLE backstage_admin
add index parent(parent)
html;
Db::query($sql);

$sql=<<<html
alter TABLE backstage_admin
add index pwd_original(pwd_original)
html;
Db::query($sql);









echo "创建<br>\n";

