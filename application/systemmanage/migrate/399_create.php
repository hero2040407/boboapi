<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_users_card (
  id     int NOT NULL AUTO_INCREMENT,
  uid    int NOT NULL DEFAULT '0' COMMENT 'uid',
  status int not null default 0 comment '1费用未支付，2正在做，3全部完成',
  create_time   int not null default 0 comment '下单时间',
  complete_time int not null default 0 comment '全部完成时间',
  admin_name    varchar(255) not null  default '' comment '是制作人员名字，不是管理员名字',
  pic varchar(255) not null  default '' comment '最终完成对模卡图片',
  money int  not null default 0 comment '本张卡片所需的费用，单位波币',
  PRIMARY KEY (id),
  index uid (uid),
  index status( status )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模块下单表'
html;
Db::query($sql);



echo "创建<br>\n";

