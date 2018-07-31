

## docker 使用命令
~~~
启动php开发套件
cd /docker_study/zphal/files && ../bin/docker-compose  up -d


进入php容器
docker exec -it files_php-fpm_1 bash

数据迁移
php ./public/index.php /command/migrate


进入mysql容器
docker exec  -it  files_mysql-db_1  bash


启动 / 停止 / 进入 swoole 容器

docker run -d --name swoole  -p 9502:9502 -p 80:80  --mount type=bind,source=/docker_study/swool_study,target=/data/www xutongle/php:7.1-fpm
docker  container stop swoole  && docker container rm swoole
docker exec  -it  swoole  bash

~~~

## mysql常用命令

查看php慢日志
tail /var/log/php-fpm/www-slow.log

查看mysql 慢日志
tail /var/lib/mysql/master-slow.log



转换表和所有字段的字符集。  

ALTER TABLE bb_starmaker_application CONVERT TO CHARACTER SET utf8mb4  

查看全部变量  
show global variables like '%log%';

设置慢查询时间
set global long_query_time=2;

查看正在执行的sql命令  

show processlist

修复表错误的sql命令。  
repair table t1;

如果单纯执行REPAIR TABLE没有起到什么效果，那么可以选择另外两个选项：  
- REPAIR TABLE EXTENDED,速度比REPAIR TABLE慢得多，但是可以修复99%的错误；  
- REPAIR TABLE USE_FRM,它会删除索引并利用table_name.frm文件中的描述重建索引，并通过table_name.MYD文件填充健对应的值。  


