<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE bb_users_achievement (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int not null default 0 comment '用户id',
  dengji tinyint not null default 0 comment '等级达人，0-3',
  zhibo tinyint not null default 0 comment '直播达人，0-3',
  pinglun tinyint not null default 0 comment '评论达人，0-3',
  dianzan tinyint not null default 0 comment '点赞达人，0-3',
  zhubo tinyint not null default 0 comment '优质主播，0-3',
  hongren tinyint not null default 0 comment 'bobo小红人，0-3',
  huodong tinyint not null default 0 comment '活动达人，0-3',
  dasai tinyint not null default 0 comment '大赛达人，0-3',
  neirong tinyint not null default 0 comment '大赛达人，0-3',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (id),
  index uid(uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户成就表'
html;
Db::query($sql);







echo "创建<br>\n";
