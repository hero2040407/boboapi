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

class Migrate {
    public function index() {
       //检查版本表，自动建立版本表
        $this->create_version_table();
        
        set_time_limit ( 0 );
        // 这里定义了一个排除的数组
        $arr_111 = array ();
        
        // 查找指定的目录，生成数组
        
        $script_path = realpath ( realpath ( APP_PATH ) . "/systemmanage/migrate" );
        $files = scandir ( $script_path );
        $temp_arr = array ();
        foreach ( $files as $value ) {
            if ($value != '.' && $value != '..' && $value != '.svn' && $value != 'data')
                $temp_arr [intval ( $value )] = $value;
        }
        if (! $temp_arr) {
            echo '没有数据';
            exit ();
        }
        
        // 按升序排列文件，很重要，决定次序
        ksort ( $temp_arr, SORT_NUMERIC );
        $sql = "select version from database_version";
        $version = Db::table('database_version')->value('version');
        // 这里是最重要的部分，加载该加载的文件
        $boo = 0; // 假定未对数据库做改变
        
        foreach ( $temp_arr as $value ) {
            if (intval ( $value ) > $version) {
                $boo = 1;
                $file_name = realpath ($script_path . '/' . $value);
                
                $this->executefile ( $file_name ); // 对数据库操作
                $this->setVersion (  $value ); // 把版本号存入特别的版本表
                $version = intval ( $value );
            }
        }
        
        echo "<br/>==========================================================================<br />";
        echo "<br/>==========================================================================<br />";
        echo '<br />current version:' . $version;
        
        if ($boo)
            echo '<br/>create table success!';
        else
            echo '<br/>no change.';
    }
    
    /**
     * 往版本表中设置值
     */
    private function setVersion($version)
    {
        $sql = "update database_version set version = " . intval($version);
       // echo $sql."<br />";
        Db::query($sql);
    }
    
    /**
     * 执行文件
     */
    private function executefile($filename) {
    
        require_once $filename;
    }
    
    /**
     * 如果没有版本表，则创建版本表
     */
    private function create_version_table()
    {
        $arr = $this->show_table();
        if (!in_array('database_version', $arr)) {
            $sql=" CREATE TABLE database_version (
                    version int NOT NULL DEFAULT '0'
                   ) ENGINE=MyISAM  ";
            Db::query($sql);
            $sql ="insert into database_version (version) values (0)";
            Db::query($sql);
        }
    }
    
    /**
     * 返回一个数组，是库里的所有表
     *
     */
    private function show_table() {
        $sql = "show tables";
        $result = Db::query($sql);
        if (! $result)
            return false;
        $temp_arr = array ();
        foreach ( $result as $value ) {
            $temp = array_values ( $value );
            $temp_arr [] = trim ( $temp [0] );
        }
        sort ( $temp_arr );
        return $temp_arr;
    }
}