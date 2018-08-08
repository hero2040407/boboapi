<?php
echo "欢迎使用数据迁移系统";

//sql语句示例

// 建表：
// CREATE TABLE log_login (
//         id int(11) NOT NULL AUTO_INCREMENT,
//         username varchar(255) NOT NULL DEFAULT '' COMMENT '登录帐号',
//         post_time int(11) NOT NULL DEFAULT '0' COMMENT '登录时间',
//         result tinyint NOT NULL DEFAULT '0' COMMENT '1成功，0失败',
//         ip varchar(255) NOT NULL DEFAULT '' COMMENT '远程ip',
//         user_type tinyint NOT NULL DEFAULT '1' COMMENT '1用户，2商户,3总站管理员，4分站管理员',
//         device_type tinyint NOT NULL DEFAULT '1' COMMENT '1pc, 2手机浏览器，3微信， 4安卓，5苹果，',
//         use_dongtai_mima tinyint NOT NULL DEFAULT '0' COMMENT '1使用动态密码登录，0普通登录',
//         create_time int not null default 0 comment '创建时间',
//         update_time int not null default 0 comment '修改时间',
//         PRIMARY KEY (id),
//         KEY username (username),
//         KEY ip (ip),
//         KEY user_type (user_type)
// ) ENGINE=myisam  DEFAULT CHARSET=utf8 COMMENT='用户和商户后台登录日志表'

// 删除表
//      drop table log_login

// 给表加字段
//      alter table log_login   add uid int not null default 0 comment '用户id'
        
// 修改表的一个字段，例如改类型
//      alter table log_login   change username username char(50) not null default '' comment '登录帐号'

// 删除表字段
//      alter table log_login drop username
        
// 给某个字段加索引
//      alter table log_login add index user_type(user_type)

//删除某个字段的索引
//      alter table log_login drop index device_type



