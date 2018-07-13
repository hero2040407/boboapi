<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_users_updates (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int not null default 0 comment '用户id',
  create_time int NOT NULL DEFAULT 0 COMMENT '创建时间',
  click_count int not null default 0 comment '点击量',
  is_remove tinyint not null default 0 comment '1删除，0正常',
  pic_count int not null default 0 comment '所有图片的数量',
  comment_count int not null default 0 comment '评论数量',
  style tinyint not null default 0 comment '1模卡， 2纯文字，3纯图片，4纯视频，4文字加图片，5文字加视频。',
  PRIMARY KEY (id),
  index uid (uid),
  index create_time (create_time),
  index style(style)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='用户动态表'

html;
Db::query($sql);






echo "创建<br>\n";

