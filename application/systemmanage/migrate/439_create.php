<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE backstage_auth (
  id int(11)  NOT NULL AUTO_INCREMENT comment '主键' ,
  module  varchar(255) not null default '' comment '模块名',
  module_key     varchar(255) not null default '' comment '键名',
  name    varchar(255) not null default '' comment '显示的值',
  roles   varchar(255) not null default '' comment '允许访问的角色名，逗号分隔，例如admin,proxy,channel',
  PRIMARY KEY (id),
  index module(module),
  index module_key(module_key),
  unique module_module_key(module,module_key)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='后台权限表'

html;
Db::query($sql);



echo "创建<br>\n";

