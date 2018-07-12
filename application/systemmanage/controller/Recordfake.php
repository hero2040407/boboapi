<?php
namespace app\systemmanage\controller;
/**
 * 定时统计，每天晚上自动执行一次，
 * 统计昨日的数据。
 * 
 * @author 谢烨
 */


use BBExtend\Sys;
use BBExtend\common\Date;
use BBExtend\DbSelect;

class Recordfake 
{
    public function index()
    {
        $db = Sys::get_container_db_eloquent();
        $sql="select * from bb_record_beifen201812 order by rand() limit 1";
        $result  = DbSelect::fetchRow($db, $sql);
        $old_id = $result['id'];
        
        unset( $result['id'] );
        $time = time();
        $result['room_id'] = strval( $time ) . mt_rand(100000,999999) . 'record_movies';
        $result['is_remove'] = 0;
        $result['audit'] = 0;
        $result['hot_days'] = 0;
        $result['real_people'] = 0;
        $result['real_like'] = 0;
        $result['dashang_bean_all'] = 0;
        $result['dashang_all'] = 0;
        $result['heat'] = 0;
        $result['like'] = 0;
        $result['look'] = 0;
        $result['time'] = $time;
        
        $new_id =  $db::table('bb_record')->insertGetId($result);
         
        // 现在做记录了！！
        $db::table('bb_record_beifen201812_log')->insert([
                'uid' => $result['uid'],
                'new_record_id'=>$new_id,
                'original_record_id'=> $old_id,
                'create_time' => $time,
        ]);
        return ['code'=>1, 'data'=>['record_id' => $new_id ]];
        
    }
    
}