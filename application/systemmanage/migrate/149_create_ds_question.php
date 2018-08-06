<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_question (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id   int          not null default 0 comment '大赛id',
  question   varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  answer     varchar(255) not null default '' comment '图片网址',
  type          tinyint not  null default 0 comment '1公告，2问答',
  question_time int      not null default 0  comment '创建时间',
  qusetion_uid  int      not null default 0 comment '提问者uid',
  answer_time   int      not null default 0  comment '回答时间',
  sort          int      not null default 0  comment '排序，大的靠前',
  PRIMARY KEY (id),
  index ds_id(ds_id)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛问答和公告表'
html;
Db::query($sql);


echo "创建<br>\n";
