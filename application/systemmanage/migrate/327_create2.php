<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE `bb_push_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `event` varchar(50) DEFAULT '0' COMMENT '推拉流的状态事件 publish表示推流 publish_done表示断流',
  `push_url` varchar(3000) NOT NULL DEFAULT '' COMMENT '新改推流地址，变长了',
  `pull_url` varchar(3000) NOT NULL DEFAULT '' COMMENT '新改拉流地址，变长了',
  `space_name` varchar(255) NOT NULL DEFAULT '' COMMENT '空间名称',
  `stream_name` varchar(255) NOT NULL DEFAULT '' COMMENT '流名称',
  `ip` varchar(255) DEFAULT NULL COMMENT '推流人的ip地址',
  `like` int(11) DEFAULT NULL COMMENT '点赞人数',
  `people` int(11) NOT NULL DEFAULT '0' COMMENT '围观人数',
  `bigpic` varchar(255) DEFAULT NULL COMMENT '封面图片',
  `title` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `sort` int(4) DEFAULT '2' COMMENT '直播类型 3玩啥 1学啥 2宝贝秀',
  `activity_id` int(4) DEFAULT '0' COMMENT '活动主题id',
  `address` varchar(120) DEFAULT NULL COMMENT '地址',
  `room_id` varchar(50) DEFAULT NULL COMMENT '房间号',
  `time` varchar(50) DEFAULT NULL,
  `heat` int(4) DEFAULT '0' COMMENT '热度 1为首屏推荐 ',
  `longitude` float(10,6) DEFAULT '0.000000' COMMENT '经度',
  `latitude` float(10,6) DEFAULT '0.000000' COMMENT '纬度',
  `stealth` int(1) DEFAULT '0' COMMENT '是否隐身',
  `flowers` int(11) DEFAULT '0' COMMENT '鲜花数量',
  `price` int(11) NOT NULL DEFAULT '0' COMMENT '波币购买价',
  `price_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1免费课程，2付费课程，3vip课程',
  `dashang_all` int(11) NOT NULL DEFAULT '0' COMMENT '打赏总额',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '后加，php接口推流开始时间，不受趣拍影响',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '用于nodejs计算时间用',
  `dashang_bean_all` int(11) NOT NULL DEFAULT '0' COMMENT '打赏波豆总额',
  `domain` varchar(255) NOT NULL DEFAULT '' COMMENT '我们设定的推送域名',
  PRIMARY KEY (`id`),
  KEY `stream_name` (`stream_name`),
  KEY `room_id` (`room_id`),
  KEY `uid` (`uid`),
  KEY `domain` (`domain`),
  KEY `event` (`event`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8


html;
Db::query($sql);



echo "创建<br>\n";
