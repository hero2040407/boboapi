<?php
namespace app\api\controller;
use BBExtend\Sys;

/**
 * 消息列表。
 * @author xieye
 *
 */
class Msg
{
    
    /**
     * 新版资源，只动图
     * 
     * $type=1,2,3,5
     * 
     * @param number $uid
     * @return number[]|string[]|number[]|array[][][]
     */
    public function index($uid,$startid=0,$length=10 ,$type=1, $token='' )
    {
        if (!in_array($type, [1,2,3,4])) {
            return ['code'=>0,'message' =>'type error' ];
        }
        if ($length> 100) {
            $length=100;
        }
        
        $user = \BBExtend\model\User::find($uid); 
            
        if (!$user) {
            return ['code'=>0,'message' =>'uid error' ];
        }
//         if (!$user->check_token($token)) {
//             return ['code'=>0,'message' =>'token error' ];
//         }
        
        $db = Sys::get_container_db();
        $use_json = input('?param.use_json')?input('param.use_json'):0; // 谢烨，2016 12
        
        $sql="select * from bb_msg 
               where uid=? and info!='' 
                 and newtype=?
               order by sort desc,id desc limit ?,?";
        $result  = $db->fetchAll($sql,[ $uid, $type, $startid, $length ]);
        
        // 设置为已读。
        $id_arr=[];
        foreach ($result as $v) {
            if ( $v['is_read']==0 ) {
                 $id_arr[]= $v['id'];
            }
        }
        if ($id_arr) {
            $sql = "update bb_msg set is_read=1 where id in (?)";
            $sql = $db->quoteInto($sql, $id_arr);
            $db->query($sql);
        }
   
        
        
        $standard_time = strtotime("2018-06-22 16:00:00");
        // 2016 12 沈德志要求。全json返回。
      //  if ($use_json) {
            
            $new = [];
            foreach ($result as $v) {
                $temp = $v;
                $temp['info'] = json_decode($temp['info'], true);
                $str='';
                foreach ($temp['info']  as $vv) {
                    if (isset($vv['content'])  ){
                      $str.= $vv['content'];
                    }
                }
                $temp['content'] = $str;
                if ($v['time'] < $standard_time ) {
                    $temp['title']='';
                }
                
                // xieye,这里遍历一下
                $url=null;
                foreach ($temp['info']  as $vv) {
                    if (isset( $vv['url'] )) {
                        $url = json_decode($vv['url'],1 );
                        break;
                    }
//                     $str.= $vv['content'];
                }
                $temp['url'] =$url;
                unset( $temp['info'] );
                unset( $temp['col1'] );
                unset( $temp['col2'] );
                unset( $temp['sort'] );
                unset( $temp['newtype'] );
                unset( $temp['overdue_time'] );
                unset( $temp['is_read'] );
                unset( $temp['uid'] );
                
                if ( $temp['pic_uid']>0 ) {
                    $temp2  = \BBExtend\model\User::find( $temp['pic_uid'] );
                    if ($temp2) {
                        $temp['img'] = $temp2->get_userpic();
                    }
                    
                }
                unset( $temp['pic_uid'] );
                
                $new[]= $temp;
                
            }
         //   $result = $new;
        //}
        $is_bottom = 1;
        if ($length== count($new)) {
            $is_bottom=0;
        }
        
        // 谢烨，现在我重新查。
        $sql="select newtype,count(*) c from bb_msg where uid=? and is_read=0 group by newtype";
        $group  = $db->fetchAll($sql,[$uid]);
        
        $count1 = $this->get_not_read_count($group, 1);
        $count2 = $this->get_not_read_count($group, 2);
        $count3 = $this->get_not_read_count($group, 3);
        $count4 = $this->get_not_read_count($group, 4);
        
        return [
                'code'=>1,
                'data'=>[
                        'not_read_count_arr' =>[
                                'system'=>$count1,
                                'like'=>$count2,
                                'fans'=>$count3,
                                'organization'=>$count4,
                                
                        ],
                        'is_bottom' =>$is_bottom,
                        'list' => $new
                ]
        ];
        
    }
    
    
    private function get_not_read_count($group,$type)
    {
        foreach ($group as $v) {
            if ( $v['newtype'] == $type ) {
                return $v['c'];
            }
        }
        return 0;
    }
    
}



