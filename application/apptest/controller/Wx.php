<?php
namespace app\apptest\controller;

use BBExtend\Sys;
class Wx
{
    
   /**
    * 该程序遍历alitemp表。
    * 发现是无效图片，则替换成。
    * 
    * http://resource.guaishoubobo.com/uploads/headpic/default.png
    * 
    * 否则，把图片换成
    * 
    */
    public function index3()
    {
        Sys::debugxieye();
        $db = Sys::get_container_db();
        $sql="select * from bb_alitemp where uid=102 
order by id
";
        $db2 = Sys::get_container_db_eloquent();
        
        $query = $db->query($sql);
        
        $moren_tupian = 'http://resource.guaishoubobo.com/uploads/headpic/default.png';
        
        $i=0;
        while ($row = $query->fetch()) {
           
            $pic = $row['url'];
            
            $uid = $row['test1'];
            
            $md5_result = md5_file($pic);
            $standard = 'fee9458c29cdccf10af7ec01155dc7f0';
            //echo $result."\n";
            if ( $md5_result === $standard ) {
                
//                 // 这是，应该把user表的pic替换成 默认图片
//                 $db2::table('bb_users')->where('uid',$uid)->update([
//                         'pic' => $moren_tupian,
//                 ]);
                
                
//                 echo "find file: {$pic}\n";
//                 echo $i++."\n";
            }else {
                $i++;
                if ($i >10) {
                   // break;
                }
                // 这里要怎样呢？，把图片上传到阿里云。然后替换成表里的头像。
                echo "find file: {$pic}\n";
              //  echo $i++."\n";
                echo  "uid=". $uid."\n";
                echo "i= {$i}\n";
                
                $help = new \BBExtend\common\Oss();
                $file = $pic;
                
                if ( is_file($file) ) {
                    echo  "uploading...\n";
                    $remote = 'uploads/headpic_date/'.date("Ymd")."/".basename($file);
                    $result= $help->upload_local_file($file, $remote);
                    echo "uploading ok: {$result}\n";
                    $db2::table('bb_users')->where('uid',$uid)->update([
                            'pic' => $result,
                    ]);
                }
                
            }
            
        }
        
    }
    
    
    
    public function get_pic2()
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select uid,pic from bb_users where permissions=1
and exists (
 select 1 from bb_users_platform
  where bb_users_platform.uid = bb_users.uid
    and bb_users_platform.type=1
)
order by uid
";
        
        $query = $db->query($sql);
        $i=0;
        while ($row = $query->fetch()) {
            $i++;
            echo $i." : ". $row['uid']."\n";
            $this->download($row['pic'],$row['uid'] );
            
            // break;
        }
    }
    
    
    private function download($pic, $uid) {
        $db = Sys::get_container_db_eloquent();
        
        if (preg_match('#^http#', $pic) && (!preg_match('#yimwing#', $pic)) ){
            $help = new \BBExtend\common\GrabImage();
            $base_dir='/mnt/uploads/weixin_headpic';
            $result =  $help->download($pic, $base_dir);
            //return $result;
            
            if ($result) {
                $values=[
                        'uid'=>102,
                        'test1'=> $uid,
                        'url' => $result,
                ];
                $db::table("bb_alitemp")->insert($values);
            }
        }
        
    }
    
    
    
    
    
    
    
    
    public function get_pic()
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select uid,pic from bb_users where permissions=1
and exists (
 select 1 from bb_users_platform
  where bb_users_platform.uid = bb_users.uid
    and bb_users_platform.type=1
)
order by uid
";
        
        $query = $db->query($sql);
        $i=0;
        while ($row = $query->fetch()) {
            $i++;
            echo $i." : ". $row['uid']."\n";
            $this->fetch($row['pic'],$row['uid'] );
            // break;
        }
        
        
        
    }
    
    
    private function fetch($pic, $uid) {
        $db = Sys::get_container_db_eloquent();
        $md5_result=  'fee9458c29cdccf10af7ec01155dc7f0';
        if (preg_match('#^http#', $pic) && (!preg_match('#yimwing#', $pic)) ){
            
            $result = md5_file($pic);
            //echo $result."\n";
            if ( $md5_result === $result ) {
                $values=[
                        'uid'=>101,
                        'test1'=> $uid,
                        'url' =>'1',
                ];
                $db::table("bb_alitemp")->insert($values);
                echo "find uid: {$uid}\n";
            }else {
                $values=[
                        'uid'=>101,
                        'test1'=> $uid,
                        'url' =>'',
                ];
                $db::table("bb_alitemp")->insert($values);
            }
             
            
        }
        
    }
    
    
    
    
    
    
    
    public function token(){
        echo Sys::get_wx_gongzhong_token();
    }
    
    
    
    
//     /**
//      * 服务端刷新微信token
//      */
//     public function sethangye()
//     {
//        $token = Sys::get_wx_gongzhong_token();
//      //  echo $token;
//        $url='http://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token='.$token;
       
//        $data = array(
//                'industry_id1' => 16,
//                'industry_id2' => 39,
//        );
       
//    //    $url = self::baseurl."/user/login/index";
//        // 发送第一个请求创建用户
//        $response = \Requests::post( $url , array(), $data);
       
//        $result = json_decode($response->body,1);
//        dump($result);
       
       
//    }
   
   
   
   
   
}
