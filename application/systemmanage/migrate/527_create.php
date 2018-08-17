<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_public_config (
  id int(11) unsigned  NOT NULL AUTO_INCREMENT,
  title varchar(255)   NOT NULL DEFAULT '' COMMENT '配置名称，例如：选手身高',
  create_time int      NOT NULL DEFAULT '0' COMMENT '创建时间',
  type    tinyint      NOT NULL DEFAULT 1 COMMENT '1单行文本，2多行文本，3复选，4单选，5下拉，6上传图片，7城市选择',
  options varchar(800) NOT NULL DEFAULT '' COMMENT '适用于类型是复选，单选，下拉的情况，使用英文逗号分隔的字符串，例如青年,少年,儿童 ',
  beizhu  varchar(255) not NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (id),
  index title(title),
  index type(type)
) ENGINE=innodb  DEFAULT CHARSET=utf8 comment="大赛配置公共选项表"

html;
Db::query($sql);


echo "创建<br>\n";

