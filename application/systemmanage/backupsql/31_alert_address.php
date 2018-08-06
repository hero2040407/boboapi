<?php

/**
 * 修改用户地址表，添加缺省值。
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_address
change  uid uid int not null default 0 comment '用户id'
html;
Db::query($sql);

$sql=<<<html
alter table bb_address
change  name name varchar(255) not null default '' comment '收货人姓名'
html;
Db::query($sql);

$sql=<<<html
alter table bb_address
change  phone phone varchar(255) not null default '' comment '收货人手机'
html;
Db::query($sql);

$sql=<<<html
alter table bb_address
change  tel tel varchar(255) not null default '' comment '固话'
html;
Db::query($sql);

$sql=<<<html
alter table bb_address
change  countries countries varchar(255) not null default '中国' comment '国家名'
html;
Db::query($sql);

$sql=<<<html
alter table bb_address
change  province province varchar(255) not null default '' comment '省'
html;
Db::query($sql);

$sql=<<<html
alter table bb_address
change  city city varchar(255) not null default '' comment '市'
html;
Db::query($sql);

$sql=<<<html
alter table bb_address
change  area area varchar(255) not null default '' comment '区'
html;
Db::query($sql);

$sql=<<<html
alter table bb_address
change  street street varchar(255) not null default '' comment '街道'
html;
Db::query($sql);

$sql=<<<html
alter table bb_address
change  zip_code zip_code varchar(255) not null default '' comment '邮编'
html;
Db::query($sql);




echo "修改用户地址表<br>\n";

