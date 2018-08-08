<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
CREATE TABLE web_article_click (
  id int   NOT NULL AUTO_INCREMENT,
  uid    int NOT NULL DEFAULT '0' COMMENT 'uid',
  news_id int not null default 0 comment '新闻id',
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id),
  index uid (uid),
  index news_id(news_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='新闻点击量表'
html;
Db::query($sql);



echo "创建<br>\n";

