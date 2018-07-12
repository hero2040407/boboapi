
## mysql常用命令

转换表和所有字段的字符集。  

ALTER TABLE bb_starmaker_application CONVERT TO CHARACTER SET utf8mb4  



查看正在执行的sql命令  

show processlist

修复表错误的sql命令。  
repair table t1;

如果单纯执行REPAIR TABLE没有起到什么效果，那么可以选择另外两个选项：  
- REPAIR TABLE EXTENDED,速度比REPAIR TABLE慢得多，但是可以修复99%的错误；  
- REPAIR TABLE USE_FRM,它会删除索引并利用table_name.frm文件中的描述重建索引，并通过table_name.MYD文件填充健对应的值。  


