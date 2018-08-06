<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\common\Date;
use BBExtend\fix\MessageType;
use think\Db;
use BBExtend\common\EmojiData;
use BBExtend\user\Comment;

use think\Config;

class Temp 
{
   
   public function area_list()
   {
       $db = Sys::get_container_db();
       $arr = ["福州","厦门","泉州","漳州","三明","莆田","南平","龙岩","宁德","平潭",];
       $arr = ["广州","深圳","珠海","汕头","佛山","韶关","湛江","肇庆","江门","茂名","惠州","梅州","汕尾","河源","阳江",
               "清远","东莞","中山","潮州","揭阳","云浮",];
       
       foreach ($arr as $area) {
           $sql="select count(*) from bb_users where address like '%{$area}%'";
           $count = $db->fetchOne($sql);
           echo $area. " : ". $count. "\n";
       }
       
   }
    
    
   public function index()
   {
       dump(   Config::get( 'bb_request_white_list_ip' ) );
   }
   
    
    public function help_comment( )
    {
        $content = $this->get_comment_content();
        
        $uid =10010;
        $record_id=354;
        
        $comment = new \BBExtend\model\UserUpdatesComment();
        $comment->create_time = time();
        $comment->uid = $uid;
        $comment->status=1; // 设置为已审核
        $comment->content = $content;
        $comment->updates_id = $record_id;
        $comment->is_reply = 0;
        $comment->save();
        
        // $article = \BBExtend\model\WebArticle::find( $record_id );
        // $article->incr(  );
        
        $db = Sys::get_container_db_eloquent();
        
        $db::table('bb_users_updates')->where("id",'=', $record_id )->increment('comment_count');
        
        return $comment->id;
        
      //  echo 123;
        
        
    }
    
    
   
    
    
    private function get_comment_content()
    {
        $arr = range(1,5);
        $emoji_one = EmojiData::get_one_str();
        $emoji_str ='';
        $count = $arr[ array_rand( $arr ) ];
        
        for($i=0; $i< $count;$i++) {
            $emoji_str .= $emoji_one;
        }
        
        // 纯文字。
        $db = Sys::get_container_db_eloquent();
        $dbzend = Sys::get_container_dbreadonly();
        $sql="select content from  bb_comment_public order by rand() limit 1";
        //$content_str = DbSelect::fetchOne($db, $sql);
        $content_str = $dbzend->fetchOne($sql);
        
        
        $content_str = trim( $content_str );
        
        // 颜文字
        $yan_str = Comment::get_one();
        //         $result='嗯嗯';
        $rand = mt_rand(1,100);
        if ($rand< 20) {
            $result = $content_str;
        }elseif ($rand < ( 20+ 30 ) ) {
            $result = $content_str . $yan_str;
        }elseif ($rand < ( 20+ 30 + 20 ) ) {
            $result = $content_str . $emoji_str;
        }elseif ($rand < ( 20+ 30 + 20 + 20 ) ) {
            $result = $yan_str;
        }else {
            $result = $emoji_str;
        }
        return $result;
        
    }
    
    
    
    public function index222(){
      
        $s = "|大赛报名时间|报名否|视频上传状态|晋级状态|重复报名|最终结果|\n";
        $s.="| ---- | :---- | :---- | :---- | :---- | :---- |\n";
        
        $arr1 = ['大赛报名中','大赛报名结束后',];
        $arr2 = ['未报名','报名成功'];
        $arr3 = ['视频未上传','视频上传审核中','视频上传审核成功','视频上传审核失败',];
        $arr4 = ['未选拔','选拔晋级','选拔淘汰',];
        $arr5 = ['允许重复报名','不允许重复报名',];
        foreach ( $arr1 as $v1 ) {
            foreach ( $arr2 as $v2 ) {
                foreach ( $arr3 as $v3 ) {
                    foreach ( $arr4 as $v4 ) {
                        foreach ( $arr5 as $v5 ) {
                            
                            $length = \BBExtend\common\Str::strlen("{$v1}{$v2}{$v3}{$v4}{$v5}" );
                            
                            $s .= "|{$v1}|{$v2}|{$v3}|{$v4}|{$v5}";
                            $temp = 30- $length;
                            foreach (range(1,$temp) as $vvv) {
                                $s .= " ";
                            }
                            $s .= "|    |\n";
                        }
                    }
                }
            }
        }
        echo $s;
        file_put_contents('d:/1.md', $s);
    }
    
    
   
}






