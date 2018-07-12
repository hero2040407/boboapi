### bb_version_android表
  
| 字段        | 类型 | 可空|缺省|其他  | 注释 |
| -------- |:------|:------|:------|
|id              | int(11) unsigned| |   |主键, 自增, |  |
| version_name   | varchar(255)    | |   || 版本号，如1.1.23 |
| version_code   | int             | |   || 整型字段版本号，如2，用于比较 |
| is_qiangzhi    | tinyint         | |   || 1强制更新，0不强制 |
| url            |varchar(255)     | |   || 下载链接 |
| update_content |varchar(255)     | |   || 此版本的更新内容 |
| create_time    | int             | |   ||创建时间 |
| update_time    | int             | |   || 更新时间 |
| admin_name     | varchar(255)    | |   || 管理员名称 |
