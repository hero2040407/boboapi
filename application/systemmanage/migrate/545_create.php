<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_thirdparty_pay_callback_log (
  id int(11) unsigned  NOT NULL AUTO_INCREMENT,
  type  varchar(255) not null default '' comment 'ali：阿里；；wx：微信',
  url  varchar(255) not null default '' comment '我的回调地址',
  money float not null default 0 comment '金钱，单位元',
  money_fen int not null default 0 comment '金钱，单位分',
  title varchar(255) not null default '' comment '标题',
  code int not null default 0 comment '支付状态',
  post_body varchar(3000) not null default '' comment '回调内容',
  create_time   int  NOT NULL DEFAULT 0  COMMENT '时间',
  PRIMARY KEY (id),
  index url (url)
) ENGINE=innodb  DEFAULT CHARSET=utf8 comment="第三方支付阿里和微信回调记录日志总表"

html;
Db::query($sql);


echo "创建<br>\n";

