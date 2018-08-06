<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
CREATE TABLE bb_subject (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL DEFAULT '' COMMENT '栏目标题',
  sort int not null default 0 comment '栏目顺序',
  is_show tinyint not null default 0 comment '1显示，0隐藏',
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='推荐栏目表'        
";
Db::query($sql);



echo "创建<br>\n";
