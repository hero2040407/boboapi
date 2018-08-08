<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE ds_race_label (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id int  not null default 0 comment '大赛id',
  label_id int  not null default 0 comment '兴趣标签id,对应bb_label表主键',
  PRIMARY KEY (id) ,
  index ds_id(ds_id),
  index label_id(label_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛标签表'
html;
Db::query($sql);


echo "创建<br>\n";
