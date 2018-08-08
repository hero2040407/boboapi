<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_comment_public (
  id int(11) NOT NULL AUTO_INCREMENT,
  content varchar(1000) NOT NULL DEFAULT '' COMMENT '随机评论内容',
  count  int NOT NULL DEFAULT 0 COMMENT '引用次数',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='随机评论内容表'
html;
Db::query($sql);




echo "创建<br>\n";
