<?php
/**
 * 谢烨，2016 10 22
 * 新的配置文件放在 application/config目录下。
 * 
 *  想改哪台机器的配置，直接修改config下的对应文件，即可。本文件不修改。
 *  
 *  245前缀，是测试服
 *  production前缀，是正式服
 *  xieye前缀，是谢烨的电脑
 *  88前缀，是88机器，
 *  200前缀，是200机器。
 *  陈岳机器，用_config.php 和 _database.php配置。
 *  
 */


return  require( dirname(__FILE__) .  
        '/config/'. strval(get_cfg_var('guaishou.username')).'_database.php' );