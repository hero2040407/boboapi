<?php

/**
 *
 * 自动更新88，远程测试服，远程正式服的数据库。
 * 得当然先传文件过去
 *
 *
 * 在本机使用，使用方法
 *
 * cd  d:\workspace_utf8\guai2
 * php .\public\index.php command/migrate/index
 *
 *
 * @author 谢烨
 *
 */


/**
 * 数据迁移帮助类
 * 使用网址
 * 
 * http://网址/systemmanage/migrate/index
 * 
 * 例如
 * http://10.0.0.88/systemmanage/migrate/index
 * http://123.59.150.245/systemmanage/migrate/index
 * 
 * 使用说明，想修改数据库结构时
 * 1、svn更新application/systemmanage目录
 * 2、在aplication/systemmanage/migrate目录下添加php文件，文件名必须以 ”数字+下划线"开头，以.php结尾
 * 3、文件内容类似
 *   <?php
        use think\Db;
        $sql="alter table bb_alitemp
                add test1 int not null default 0 comment '测试字段'  ";
        Db::query($sql);
        //xieye注：请勿直接打印sql语句，防止不相关的人看到。
        echo "修改了alitemp";
     ?>   
 * 
 * 4、如果在本机上修改，直接在浏览器输入上述网址，
 * 5、如果想在任何服务器（包括正式和测试），只需application/systemmanage目录更新，然后浏览器打网址即可。
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;

use think\Db;

class Backup {
    public function index() {
       //检查版本表，自动建立版本表
      $tables = \BBExtend\common\MysqlTool::show_tables();
     // dump($tables);
      $paichu_arr=['bb_tongji_log','bb_tongji_user_huizong', 'bb_request' ];
      $i=0;
      
      $dir = "/mnt/backup/mysql_backup/".date("Ymd")."/";
      
      foreach ( $tables as $table ) {
          $i++;
          if ( in_array($table, $paichu_arr ) ) {
              $command ="/usr/bin/mysqldump -uroot -pChenyueAbc.123  --no-data bobo {$table}  > {$dir}{$table}.sql";
          }else {
              $command ="/usr/bin/mysqldump -uroot -pChenyueAbc.123 bobo {$table}  > {$dir}{$table}.sql";
          }
          $out = shell_exec ( $command );
          echo "backup {$table}  ok. \n"; 
          if ($i>1) {
       //       break;
          }
          
      }
      
    }
    
   
}