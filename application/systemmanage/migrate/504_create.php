<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_users_updates_like_log (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  type tinyint not null default 0 comment '1动态，2评论，3回复',
  updates_id int(11) NOT NULL DEFAULT '0' COMMENT '动态id,或者评论id,或者回复id，根据type决定',
  PRIMARY KEY (id),
  index updates_id(updates_id),
  index uid(uid)
) ENGINE=innodb  DEFAULT CHARSET=utf8mb4 COMMENT='动态点赞日志表'

html;
Db::query($sql);






echo "创建<br>\n";

