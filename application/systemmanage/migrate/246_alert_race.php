<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_game (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  title varchar(255) NOT NULL DEFAULT '' COMMENT '活动名称',
  url varchar(255) NOT NULL DEFAULT '' COMMENT '网址',
  start_time int not null default 0 comment '活动开始时间戳，0为已开始',
  end_time int not null default 0 comment '活动结束时间戳，0为未开始',
  is_active tinyint not null default 0 comment '1有效，0无效',
  type tinyint(4) NOT NULL DEFAULT 0 COMMENT '一般与id一致。表示活动类型',
  info varchar(1000) not null default '' comment '活动信息',
  is_html  tinyint(4) NOT NULL DEFAULT 0 COMMENT '1代表是开html页面，0代表app自身功能',     
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='特别策划表' 
html;
Db::query($sql);






echo "创建<br>\n";
