<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE web_article_like (
  id          int(11) NOT NULL AUTO_INCREMENT,
  bigtype        tinyint not null default 1 comment '1新闻评论及回复',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  uid         int NOT NULL DEFAULT 0   COMMENT '用户id',
  comment_id  int NOT NULL DEFAULT '0' COMMENT '根据type来定，type为1表示是新闻评论及回复的id',
  PRIMARY KEY (id),
  index uid (uid),
  index comment_id(comment_id),
  index bigtype(bigtype)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment='新闻评论回复点赞日志表'

html;
Db::query($sql);



echo "创建<br>\n";
