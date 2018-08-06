<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE web_article_comment (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  article_id int      not null default 0 comment '新闻id',
  uid        int      not null default 0 comment '用户id',
  content varchar(3000) not null default '' comment '评论内容',
  status     tinyint  not null default 0 comment '0未审核，1已审核',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment='新闻评论表'
html;
Db::query($sql);



echo "创建<br>\n";
