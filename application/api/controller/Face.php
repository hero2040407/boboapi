<?php
namespace app\api\controller;
use BBExtend\Sys;
use BBExtend\DbSelect;
/**
 * 人脸贴图
 * @author xieye
 *
 */
class Face
{
    
    const max_count=54;
    
    private function get_row($row)
    {
        $new = [];
        $new['id']=$row['id'];
        $new['title'] = $row['title'];
        
        $new['pic']=  \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $row['pic'] );
        $new['link']=  \BBExtend\common\PicPrefixUrl::add_pic_prefix_https(  $row['link'] );
        $new['pic_gray']=  \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $row['pic_gray'] );
        if (!$new['pic_gray']) {
            $new['pic_gray']= $new['pic'];
        }
        
        return $new;
    }
    
    
    
    public function download($uid, $resource_id,$type=1)
    {
        $db = Sys::get_container_db_eloquent();
        if ($type==1) {
        
            $hot_key ='face_hot_key';
            $new_key = 'face_new_key';
            //原理，都是list，只要有，就是最新。插入左边，消减。然后，
            $redis = Sys::get_container_redis();
            $redis->lRem($new_key, $resource_id, 1000);//去除重复
            $redis->lPush($new_key, $resource_id);
            $redis->lTrim($new_key, 0, self::max_count);// 修剪
            
            // 对于最热，使用
            $redis->hIncrBy($hot_key, strval($resource_id), 1);
            
            
            try{
          //      $db::table('bb_user_face')->insert(['uid' =>$uid,'face_id'=> $face_id ]);
            }catch (\Exception $e){
                
            }
        
        }
        
        // 2表示动图
        if ($type==2) {
            
            $hot_key ='gif_hot_key';
            $new_key = 'gif_new_key';
            //原理，都是list，只要有，就是最新。插入左边，消减。然后，
            $redis = Sys::get_container_redis();
            $redis->lRem($new_key, $resource_id, 1000);//去除重复
            $redis->lPush($new_key, $resource_id);
            $redis->lTrim($new_key, 0, self::max_count);// 修剪
            
            // 对于最热，使用
            $redis->hIncrBy($hot_key, strval($resource_id), 1);
            
            
            try{
                //      $db::table('bb_user_face')->insert(['uid' =>$uid,'face_id'=> $face_id ]);
            }catch (\Exception $e){
                
            }
            
        }
        
        return ['code'=>1, ];
    }
    
    
    
    
    public function index($uid=0)
    {
//         if (!$uid) {
//             return ['code'=>0,'message'=>'暂未开通' ];
//         }
        
        
        $db = Sys::get_container_dbreadonly();
        $redis = Sys::get_container_redis();
        $sql="select count(*) from bb_users_test where uid=?";
        $count = $db->fetchOne( $sql,[ $uid ]);
        if (!$count) {
   //         return ['code'=>0,'message'=>'暂未开通' ];
        }
        
        $my = [];
        $my['id'] = -3;// 我的
        $my['pic'] = 'http://resource.guaishoubobo.com/public/face/img_my_click@3x.png';
        $my['pic_gray'] = 'http://resource.guaishoubobo.com/public/face/img_my@3x.png';
        $list = [];
        
        
        $my['list'] = $list;
        
        
        $hot_key ='face_hot_key';
        $new_key = 'face_new_key';
        
        $hot['id'] = -2;// 最热
        $hot['pic'] = 'http://resource.guaishoubobo.com/public/face/zuire.png';
        $hot['pic_gray'] = 'http://resource.guaishoubobo.com/public/face/zuire_gray.png';
        $result = $redis->hGetAll($hot_key)  ;
        // 对temp排序。
        arsort($result);
        $result = array_keys($result);
        
        $list=[];
        $count = self::max_count;
        $i=0;
        foreach ( $result as $v ) {
            $sql="select * from bb_face where id=?";
            $temp = $db->fetchRow($sql, $v);
            if ($i++ > $count) {
                break;
            }
            if ($temp) {
                $list[]= [
                        'id' => $temp['id'],
                        'pic' => $temp['pic'],
                        'link' => $temp['link'],
                ];
            }
        }
        $hot['list'] = $list;
        
        
        
        $new2['id'] = -1;// 最新
        $new2['pic'] = 'http://resource.guaishoubobo.com/public/face/zuixin.png';
        $new2['pic_gray'] = 'http://resource.guaishoubobo.com/public/face/zuixin_gray.png';
       // $result = $redis->lRange($new_key, 0, -1);
       
        // 对于最新做特别处理，仿造普通
        $sql="select id from bb_face  where parent > 0  
              order by create_time desc limit ". self::max_count;
        $result = $db->fetchCol($sql);
        
        
        $list=[];
        foreach ( $result as $v ) {
            $sql="select * from bb_face where id=?";
            $temp = $db->fetchRow($sql, $v);
            if ($temp) {
                $list[]= [
                        'id' => $temp['id'],
                        'pic' => $temp['pic'],
                        'link' => $temp['link'],
                ];
            }
        }
        $new2['list'] = $list;
        
        
        $sql="select * from bb_face
                order by sort desc
                ";
        $result = $db->fetchAll($sql);
        $new=[];
        foreach ($result as $v){
            if ($v['parent']==0) {
                $temp= $this->get_row( $v );
                $temp['list']=[];
                unset($temp['link']);
                $new[]= $temp;
            }
        }
     //   Sys::debugxieye($new);
        
        
        foreach ($new as $k=>$v) {
            foreach ($result as $v2){
                if ( $v2['parent'] && $v2['parent']== $v['id'] ) {
                    $temp = $this->get_row($v2);
                    unset($temp['pic_gray'] );
                    
                    $new[$k]['list'][]= $temp;
                    
                }
            }
        }
        $arr=[];
        $arr[]= $my;
        $arr[]= $hot;
        $arr[]= $new2;
        foreach ($new as $v) {
            $arr[] = $v;
        }
        return ['code'=>1,'data'=>[ 'list'=> $arr ],      ];
        
    }
}
