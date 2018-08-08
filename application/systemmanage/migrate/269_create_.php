<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE bb_users_achievement_bonus (
        
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int not null default 0 comment '用户id',      
  level tinyint not null default 0 comment '当前级别',
  event varchar(255) not null default '' comment 'dengji,zhibo,pinglun,dianzan,zhubo,hongren,huodong,dasai,neirong中一项',      
  bonus int not null default 0 comment '波币奖励',
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id),
  KEY uid(uid),
  KEY level(level),
  KEY event(event)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment='用户成就奖励表'
html;
Db::query($sql);







echo "创建<br>\n";
