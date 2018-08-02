<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/21
 * Time: 23:01
 */

namespace BBExtend;
use think\Db;
use think\Config;

class BBRedis
{
    private $redis;
    //当前数据库ID号
    protected $dbId=0;

    private  static  $_instance;

    private function __construct()
    {
        $this->mArray=array();
        $this->redis = self::connectionRedis();
        return NULL;
    }

    private function __clone()
    {
    }
    
    /**
     * 通用函数
     * 
     * 其余地方的调用 : \BBExtend\BBRedis::connectionRedis()
     * 
     * @return \Redis
     */
    public static function connectionRedis(){
        // xieye 2016 1020 不用长连接
        $redis = new \Redis();
        $redis -> connect(Config::get('REDIS_HOST'),Config::get('REDIS_PORT'));
        $redis -> auth(Config::get('REDIS_AUTH'));
        return $redis;
    }
    
    public static function getInstance($DBName)
    {
        $mArray=array('monster'=>0,'record'=>1,'bb_task'=>2,'chat_room'=>3,'user'=>4,'activity'=>5,'expression'=>6,'config'=>7,'push'=>8,'io_socket'=>9,'comments'=>10);
        if(is_null(self::$_instance)){
            self::$_instance = new BBRedis;
        }
        $DBNum=0;
        foreach ($mArray as $key=>$keyvalue) {

            if ($key===$DBName)
            {
                $DBNum=$keyvalue;

                break;
            }
        }
        self::$_instance->redis->select($DBNum);

        return self::$_instance;

    }

    /**
     * 得到hash表中一个字段的值
     * @param string $key 缓存key
     * @param string  $field 字段
     * @return string|false
     */
    public function hGet($key,$field)
    {
        return $this->redis->hGet($key,$field);
    }
    public function Get($Key)
    {
        return $this->redis->get($Key);
    }
    /**
     * 为hash表设定一个字段的值
     * @param string $key 缓存key
     * @param string  $field 字段
     * @param string $value 值。
     * @return bool
     */
    public function hSet($key,$field,$value)
    {
        \BBExtend\Sys::debugxieye("bbredis:hset:{$key}:{$field}");
        return $this->redis->hSet($key,$field,$value);
    }

    
    /**
     * xieye,给某个哈希的键加1
     * @param unknown $key
     * @param unknown $field
     * @param unknown $value
     */
    public function hIncrBy($key,$field,$count=1)
    {
        return $this->redis->hIncrBy($key,$field,$count);
    }
    
    public function Set($key,$value)
    {
        \BBExtend\Sys::debugxieye("bbredis:set:{$key}:");
        return $this->redis->set($key,$value);
    }
    /**
     * 判断hash表中，指定field是不是存在
     * @param string $key 缓存key
     * @param string  $field 字段
     * @return bool
     */
    public function hExists($key,$field)
    {
        return $this->redis->hExists($key,$field);
    }
    /**
     * 删除hash表中指定字段 ,支持批量删除
     * @param string $key 缓存key
     * @param string  $field 字段
     * @return int
     */
    public function hdel($key,$field)
    {
        $fieldArr=explode(',',$field);
        $delNum=0;

        foreach($fieldArr as $row)
        {
            $row=trim($row);
            $delNum+=$this->redis->hDel($key,$row);
        }

        return $delNum;
    }
    public function Del($key)
    {
        return $this->redis->del($key);
    }
    /**
     * 返回hash表元素个数
     * @param string $key 缓存key
     * @return int|bool
     */
    public function hLen($key)
    {
        return $this->redis->hLen($key);
    }

    /**
     * 为hash表设定一个字段的值,如果字段存在，返回false
     * @param string $key 缓存key
     * @param string  $field 字段
     * @param string $value 值。
     * @return bool
     */
    public function hSetNx($key,$field,$value)
    {
        return $this->redis->hSetNx($key,$field,$value);
    }
    /**
     * 为hash表多个字段设定值。
     * @param string $key
     * @param array $value
     * @return array|bool
     */
    public function hMset($key,$value)
    {
        return true;
        \BBExtend\Sys::debugxieye("bbredis:hmset:{$key}:");
        if(!is_array($value))
            return false;
        return $this->redis->hMset($key,$value);
    }

    /**
     * 为hash表多个字段设定值。
     * @param string $key
     * @param array|string $value string以','号分隔字段
     * @return array|bool
     */
    public function hMget($key,$field)
    {
        if(!is_array($field))
            $field=explode(',', $field);
        return $this->redis->hMget($key,$field);
    }
    /**
     * 返回所有hash表的所有字段
     * @param string $key
     * @return array|bool
     */
    public function hKeys($key)
    {
        return $this->redis->hKeys($key);
    }
    /**
     * 返回所有hash表的字段值，为一个索引数组
     * @param string $key
     * @return array|bool
     */
    public function hVals($key)
    {
        return $this->redis->hVals($key);
    }
    /**
     * 返回所有hash表的字段值，为一个关联数组
     * @param string $key
     * @return array|bool
     */
    public function hGetAll($key)
    {
        return $this->redis->hGetAll($key);
    }
    public function hGetAllKey()
    {
        return $this->redis->keys('*');
    }
    public  function GetRandom()
    {
        $key = $this->redis->randomKey();
        return $this->hGetAll($key);
    }
    /**

     * 清空当前数据库

     * @return bool

     */
    public function flushAll()
    {
        return $this->redis->flushAll();
    }
    public function flushDB()

    {

        return $this->redis->flushDB();

    }
}