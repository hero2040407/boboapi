<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE web_article_media (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  article_id int(1) NOT NULL DEFAULT '0' comment '文章id',
  url        varchar(255) NOT NULL DEFAULT '' comment '网址',
  media_type tinyint  NOT NULL DEFAULT 0 COMMENT '1图片,2视频',
  sort        int     not null default 0 comment '大于零，则在新闻列表首页显示，为零则不显示。从小到大排序。',
  create_time int(11) NOT NULL DEFAULT '0' comment '创建时间',
  time_length varchar(255) not null default '' comment 'media_type=2才有效，值类似01:15，或02:30:11，必须英文冒号',
  PRIMARY KEY (id),
  index article_id(article_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8

html;
Db::query($sql);



echo "创建<br>\n";
