<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_users_updates_comment (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  updates_id int(11) NOT NULL DEFAULT '0' COMMENT '动态id,对于回复是动态评论的id',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  content varchar(3000) NOT NULL DEFAULT '' COMMENT '评论内容',
  status tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未审核，1已审核',
  is_reply tinyint(4) NOT NULL DEFAULT '0' COMMENT '0评论，1对评论的回复',
  reply_count int(11) NOT NULL DEFAULT '0' COMMENT '回复的数量，只针对评论而言，对回复无意义',
  like_count int(11) NOT NULL DEFAULT '0' COMMENT '点赞的数量，评论和回复都有的',
  reply_time int(11) NOT NULL DEFAULT '0' COMMENT '对于该评论的回复的最新时间，对于回复此字段无意义',
  PRIMARY KEY (id),
  index updates_id(updates_id)
) ENGINE=innodb  DEFAULT CHARSET=utf8mb4 COMMENT='动态评论表'

html;
Db::query($sql);






echo "创建<br>\n";

