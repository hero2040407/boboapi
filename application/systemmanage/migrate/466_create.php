<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE backstage_auth_list (
  id       int NOT NULL AUTO_INCREMENT,
  module     varchar(255) NOT NULL DEFAULT '' COMMENT '模块名',
  module_key varchar(255) NOT NULL DEFAULT '' COMMENT '键名',
  name       varchar(255) NOT NULL DEFAULT '' COMMENT '显示的值',
  PRIMARY KEY (id),
  UNIQUE KEY module_module_key (module,module_key),
  KEY module (module),
  KEY module_key (module_key)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='所有权限表'
html;
Db::query($sql);



echo "创建<br>\n";

