<?php

/**
 * bb_buy
 * 修改充值表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_buy
add third_name varchar(255) NOT NULL DEFAULT '' COMMENT '第三方支付名称'
html;


Db::query($sql);


$sql=<<<html
alter table bb_buy
add third_serial varchar(255) NOT NULL DEFAULT '' COMMENT '第三方支付订单号，反查第三方用'
html;

Db::query($sql);




echo "修改充值表<br>\n";

