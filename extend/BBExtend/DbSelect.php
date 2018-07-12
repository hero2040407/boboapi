<?php

namespace BBExtend;
/**
 *                             _ooOoo_
 *                            o8888888o
 *                            88" . "88
 *                            (| -_- |)
 *                            O\  =  /O
 *                         ____/`---'\____
 *                       .'  \\|     |//  `.
 *                      /  \\|||  :  |||//  \
 *                     /  _||||| -:- |||||-  \
 *                     |   | \\\  -  /// |   |
 *                     | \_|  ''\---/''  |   |
 *                     \  .-\__  `-`  ___/-. /
 *                   ___`. .'  /--.--\  `. . __
 *                ."" '<  `.___\_<|>_/___.'  >'"".
 *               | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *               \  \ `-.   \_ __\ /__ _/   .-` /  /
 *          ======`-.____`-.___\_____/___.-`____.-'======
 *                             `=---='
 *          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 *                    佛祖保佑                 永无BUG
 *               本模块已经经过开光处理，绝无可能再产生bug
 *          =============================================
 */

/**
 * Eloquent数据库框架帮助类。确保结果集返回数组。
 * @author Administrator
 *
 */
class DbSelect {
    /**
     * 获取常规的数据库查询结果，
     * 假如结果集为空，返回[]
     * 
     * @param \Illuminate\Database\Capsule\Manager $db laravel5框架的Eloquent的DB对象  
     * @param string $sql 查询语句
     * @param array $bind  绑定参数
     * return array 二维数组
     */
    public static function fetchAll($db, $sql, $bind=[]) 
    {
        $result = collect( $db::select($sql,$bind ))->map(function ($v,$k){ 
            return get_object_vars($v);  
        })->toArray() ;
        return $result;
    }
    
    /**
     * 获取数据库查询结果，是单列的，
     * 假如sql语句中包含多列，只取第一列
     * 假如结果集为空，返回[]
     *
     * @param \Illuminate\Database\Capsule\Manager $db laravel5框架的Eloquent的DB对象 
     * @param string $sql 查询语句
     * @param array $bind  绑定参数
     * return array 一维数组
     */
    public static function fetchCol($db, $sql, $bind=[]) 
    {
        $result = collect( $db::select($sql,$bind ))->map(function ($v,$k){ 
            $a = get_object_vars($v);
            foreach ($a as $k2=> $v2) {
                return $v2;
            }
        })->toArray();
        return $result;
    }
    
    /**
     * 获取数据库查询结果，是单个值，标量
     * 假如sql语句中包含多列，只取第一列
     * 假如结果集有多行，只取第一行
     * 假如结果集为空，返回null
     *
     * @param \Illuminate\Database\Capsule\Manager $db laravel5框架的Eloquent的DB对象 
     * @param string $sql 查询语句
     * @param array $bind  绑定参数
     * return mixed 整型|字符串|浮点型
     */
    public static function fetchOne($db, $sql, $bind=[])
    {
        $result = collect( $db::select($sql,$bind ))->map(function ($v,$k){
            if ($k==0) {
                $a = get_object_vars($v);
                foreach ($a as $k2=> $v2) {
                    return $v2;
                }
            }
        })->toArray();
        if ($result) {
            $result = $result[0];
        }else {
            $result = null;
        }
        return $result;
    }
    
    /**
     * 获取数据库查询结果，只取第一行
     * 假如结果集为空，返回null
     *
     * @param \Illuminate\Database\Capsule\Manager $db laravel5框架的Eloquent的DB对象 
     * @param string $sql 查询语句
     * @param array $bind  绑定参数
     * return array 一维数组,对应表中的一行。
     */
    public static function fetchRow($db, $sql, $bind=[])
    {
        $result = collect( $db::select( $sql,$bind ))->map(function ($v,$k){
            if ($k==0){
              return get_object_vars($v);
            }else {
              return null;
            }
        })->toArray() ;
        if ($result) {
            $result = $result[0];
        }else {
            $result = null;
        }
        return $result;
    }

}