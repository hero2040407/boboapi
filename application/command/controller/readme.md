
## 文件说明
readme.md　说明文件
守护进程是两个php
Worker.php
Worker22.php
Worker23.php


分别对应2个任务文件
Workerjob.php    任务文件， 直播时，需一下子对所有人发送。type：124
Job22.php        任务文件，119视频点赞，121打赏，122被关注，123好友上传视频
Job23.php        任务文件，获得微信统一id
test3.php        测试队列是否运行的文件
init_demo.php    用处不大，暂保留


## 队列启动
super启动命令,注意，根据配置会自动启动两个worker
supervisord -c /etc/supervisord.conf
/root/xieyeshell/w1.sh
/root/xieyeshell/w2.sh
/root/xieyeshell/phean.sh


super停止命令
ps aux|grep supervisord
然后kill

ps aux|grep worker
然后kill /command/worker/start
然后kill /command/worker22/start

ps aux|grep phean
然后kill /root/xieyeshell/phean.sh
然后kill /systemmanage/phean/run



=============================
两个php守护进程，测试方式，测存在，数据库连接正确性
bb_alitemp表先清空。
 php /var/www/html/application/command/controller/test3.php
测试  

然后，检查bb_alitemp表里的数据！

============================

这个命令是把半小时合并推送,临时性执行一次
/usr/bin/php /var/www/html/public/index.php /systemmanage/live/push_message
