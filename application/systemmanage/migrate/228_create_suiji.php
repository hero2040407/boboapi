<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_user_suiji (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='随机用户表'   
html;
Db::query($sql);







echo "创建<br>\n";
