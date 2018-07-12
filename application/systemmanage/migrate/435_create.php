<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_bottom_bar_rule (
  id int(11) unsigned NOT NULL AUTO_INCREMENT comment '主键就相当于版本号，id越大越优先' ,
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间，必须记录',
  start_time int(11) NOT NULL DEFAULT '0' COMMENT '规则：开始时间，为0表示没有限制', 
  end_time int(11) NOT NULL DEFAULT '0' COMMENT '规则：结束时间，为0表示没有限制',
  same_rule_id int(11) NOT NULL DEFAULT '0' COMMENT '为防止图片重复上传用的， 一般为0，假如不为0，则必须是本表中已有的其他主键，图片就以这个规则主键去查',
  zip_path   varchar(255) not null default '' comment '这个值必须有，是zip文件的网址，必须有域名',
  PRIMARY KEY (id)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='app底部导航规则表'
html;
Db::query($sql);

$sql=<<<html
CREATE TABLE bb_bottom_bar_pic (
  id int(11) unsigned NOT NULL AUTO_INCREMENT comment '主键就相当于版本号，id越大越优先' ,
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间，必须记录。',
  rule_id int(11) NOT NULL DEFAULT '0' COMMENT '规则id， 对应(app底部导航规则表)的主键id',
  pic_key varchar(255) not null default '' comment '图标的键，目前只能是show,friend,live,activity,my中的一个',
  size varchar(255) not null default '' comment '图标尺寸，只能是2x,3x中的一个',
  style varchar(255) not null default '' comment '图标样式，只能是color,gray中的一个',
  index rule_id(rule_id),
  PRIMARY KEY (id)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='app底部导航图片表'
html;
Db::query($sql);




echo "创建<br>\n";

