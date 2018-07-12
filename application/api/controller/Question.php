<?php
namespace app\api\controller;
use BBExtend\Sys;


class Question
{
    /**
     * 返回
     */
    public function hot()
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_question_official where sort > 0 and is_valid=1 order by sort desc limit 5";
        $result = $db->fetchAll($sql);
        
        return ['code'=>1, 'data' =>['list' =>$result ] ];
    }
    
    /**
     * 返回
     */
    public function index($type)
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_question_official where type=? and is_valid=1 ";
        $result = $db->fetchAll($sql,[ $type ]);
        
        return ['code'=>1, 'data' =>['list' =>$result ] ];
    }
    
    /**
     * 返回
     */
    public function detail($id)
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_question_official where id=? and is_valid=1 ";
        $result = $db->fetchRow($sql,[ $id ]);
        if (!$result) {
            return ['code'=>0,'message' =>'id err' ];
        }
        return ['code'=>1, 'data' =>$result ];
    }
    
   
   
}
