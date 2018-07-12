<?php
namespace BBExtend\common;
use think\Config;
use Illuminate\Database\Capsule\Manager;
use BBExtend\common\SqlListen;

use BBExtend\Sys;

/**
 * 获取db对象，代码中请勿直接使用此类，应使用BBExtend\Sys::get_container_db()调用。
 * 
 * 使用单件模式，允许在单元测试时注入。
 *  
 * @author 谢烨
 */
class ZendDb
{
    public static $db;
    public static $db_read;
    
    public static $db2;
    public static $db_eloquent;
    public static $db_eloquent_readonly;
    //public static $db_eloquent;
    
    private function __construct(){}
    
    /**
     * 返回一个zf1的数据库连接
     * @return \Zend_Db_Adapter_Abstract
     */
    public static function getdb()
    {
        if (self::$db == null) {
            self::$db = \Zend_Db::factory('pdo_mysql', self::get_db_param());
        }
        return self::$db;
    }
    
    
    /**
     * 返回一个zf1的数据库连接
     * @return \Zend_Db_Adapter_Abstract
     */
    public static function getdb_read()
    {
        if (self::$db_read == null) {
            $config = self::get_db_param();
            
            if (Sys::is_product_server()) {
                $config['host'] = '10.26.24.151';
            }
            if ( Sys::get_machine_name()=='245' ) {
              //  $config['port'] = '3307';
            }
            
            self::$db_read = \Zend_Db::factory('pdo_mysql', $config);
        }
        return self::$db_read;
    }
    
    /**
     * 返回另一个zf1的数据库连接
     * @return \Zend_Db_Adapter_Abstract
     */
    public static function getdb2()
    {
        if (self::$db2 == null) {
            self::$db2 = \Zend_Db::factory('pdo_mysql', self::get_db_param());
        }
        return self::$db2;
    }
    
    /**
     * 构造创建zenddb对象需要的数组
     * @return array
     */
    private static function get_db_param()
    {
        $params = array(
            'host'     => Config::get('database.hostname'),
            'username' => Config::get('database.username'),
            'password' => Config::get('database.password'),
            'dbname'   => Config::get('database.database'),
            'charset'  => Config::get('database.charset'),
            'port'     => Config::get('database.hostport'),
            'driver_options'=> [
                \PDO::ATTR_STRINGIFY_FETCHES => false,
                \PDO::ATTR_EMULATE_PREPARES  => false,
            ],
        );
        return $params;
    }
    /**
     *
     * @return \Illuminate\Database\Capsule\Manager
     */
    public static function getdb_eloquent()
    {
        if (self::$db_eloquent == null) {
            // self::$db_eloquent = \Zend_Db::factory('pdo_mysql', self::get_db_param());
    
            $db = new Manager ();
            $db->addConnection ( [
                'driver' => 'mysql',
                'host' => Config::get('database.hostname'),
                'database' => Config::get('database.database'),
                'username' => Config::get('database.username'),
                'password' => Config::get('database.password'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => ''
            ] );
            $db->setAsGlobal ();
            $db->bootEloquent ();
           
            if (!\BBExtend\Sys::is_product_server()) {
                // 设置sql日志监听
             //   $db->setEventDispatcher ( new SqlListener () );
            }
          //      ee2;
            self::$db_eloquent = $db;
        }
        return self::$db_eloquent;
    }
    
    public static function getdb_eloquent_readonly()
    {
        if (self::$db_eloquent_readonly == null) {
            // self::$db_eloquent = \Zend_Db::factory('pdo_mysql', self::get_db_param());
            
            $db = new Manager ();
            $db->addConnection ( [
                    'driver' => 'mysql',
                    'host' => '10.26.24.151',
                    'database' => Config::get('database.database'),
                    'username' => Config::get('database.username'),
                    'password' => Config::get('database.password'),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => ''
            ] );
          //  $db->setAsGlobal ();
          //  $db->bootEloquent ();
            
            if (!\BBExtend\Sys::is_product_server()) {
                // 设置sql日志监听
                //   $db->setEventDispatcher ( new SqlListener () );
            }
            //      ee2;
            self::$db_eloquent_readonly = $db;
        }
        return self::$db_eloquent_readonly;
    }
    
  
}//end class

