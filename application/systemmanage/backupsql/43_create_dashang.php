<?php

/**
 * 创建打赏表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_dashang_log (
  id               int           NOT NULL AUTO_INCREMENT,
  uid              int           not null default 0  comment '打赏人，是付钱的人',
  movie_id         int           not null default 0  comment '被打赏视频id',
  target_uid       int           not null default 0  comment '被打赏人uid',      
  create_time      int           not null default 0  comment '当前时间',
  gold             int           not null default 0  comment '打赏波币数量',
  PRIMARY KEY (id),
  index movie_id(movie_id),
  index target_uid(target_uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment "打赏日志表"        
html;
Db::query($sql);


echo "创建bb_dashang_log表<br>\n";
