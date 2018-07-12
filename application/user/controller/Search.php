<?php
/**
 * 交友模块
 */

namespace app\user\controller;

use BBExtend\Sys;
use BBExtend\model\UserDetail;


class Search
{
    
    /**
     * 过滤sql，使得结果可以用于like语句
     * @param unknown $s
     */
    private  function filter_str($s)
    {
        //先把换行改成空格
        $pattern = '/(\r\n|\n)/s';
        $s = preg_replace($pattern, '', $s);
        //20-7e 包括了0－9a-zA-Z空格，英文标点。是ascii表的主要一部分
        // 4e00- 9fa5 全部汉字，但不含中文标点
        $pattern = '/[^\x{4e00}-\x{9fa5}0-9a-zA-Z]/u';
        $s = preg_replace($pattern, '', $s);
        return $s;
    }

    /**
     * 检索。
     */
    public function index ( $uid,$token, $search_content = '' )
    {
        //谢烨20160928，修改模糊查找，加防注入
        $search_content_backup = trim($search_content) ;
        if (empty( $search_content_backup )) {
            return ['code'=>0,'message'=>'请填写搜索内容'];
        }
        
        $search_content = $this->filter_str($search_content);
        
        $uid = intval($uid);
        $user = UserDetail::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        
        $UserDB_Array  =[];
        $db = \BBExtend\Sys::get_container_dbreadonly();
        
        if (is_numeric($search_content)) {
            $sql = "select * from bb_users where uid='{$search_content}'
              or nickname like '%{$search_content}%'
              limit 30
            ";
        }else {
            if ($search_content) {
                $sql = "select * from bb_users where  nickname like '%{$search_content}%'
              limit 30
              ";
            }else {
                $sql = "select * from bb_users where  uid=0";
            }
        }
        
        $UserDB_Array  = $db->fetchAll($sql);
        if (!$UserDB_Array) {
            $sql = "select * from bb_users where  nickname =? limit 30";
            $UserDB_Array  = $db->fetchAll($sql,$search_content_backup);
        }
        
        
        $new = array();
        foreach ($UserDB_Array as $UserDB)
        {
            $temp = UserDetail::find( $UserDB['uid'] );
            $new[]= $temp->get_jiav_focus($uid)  ;
        }
        return ['data'=>['list' => $new ],'code'=>1];
    }
    
   
  

}

