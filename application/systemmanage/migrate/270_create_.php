<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_users_achievement_msg (
        
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int not null default 0 comment '用户id',
  is_read tinyint not null default 0 comment '默认0未读',
        
  level tinyint not null default 0 comment '当前级别',
  event varchar(255) not null default '' comment 'dengji,zhibo,pinglun,dianzan,zhubo,hongren,huodong,dasai,neirong中一项',
  event_name varchar(255) not null default '' comment 'event汉字名称',      
  bonus int not null default 0 comment '波币奖励',
  pic  varchar(255) not null default '成就图片',
  content varchar(1000) not null default '' comment '',
        
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id),
  KEY uid(uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment='用户成就消息表'
html;
Db::query($sql);







echo "创建<br>\n";
