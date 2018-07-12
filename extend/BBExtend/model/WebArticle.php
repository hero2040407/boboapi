<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;


/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class WebArticle extends Model 
{
    protected $table = 'web_article';
    
    public $timestamps = false;
    
    
    public function info()
    {
        $db = Sys::get_container_db_eloquent();
        $new=[];
        $new['id']=$this->id;
        $new['title'] = $this->title;
        $new['type']  = $this->style;
        $new['source'] = $this->source;
        $new['comments'] = $this->comment_count ;
        $new['time'] = $this->create_time;
        $new['pic_count'] = $this->pic_count;
        
        //1：纯文字，2：文字1大图，3：文字3小图，4：大视频，5：小视频
        $media=[];
        if ( $this->style==4 || $this->style==5 ) {
            $sql="select url, time_length 
               from web_article_media where article_id= ?
                and media_type=2
               " ;
            $temp = DbSelect::fetchRow($db, $sql, [ $this->id ]);
            $media['url'] = $temp['url'];
            $media['time_length'] = $temp['time_length'];
            
        }
        $new['video'] = $media;
        
        $pic =[];
        
        $limit =1;
        if ($this->style==3) {
            $limit = 3;
        }
        
        if ( $this->style==2 || $this->style==3 || $this->style==4 || $this->style==5 ) {
            $sql = "select *               
                 from web_article_media where article_id= ?
                  and media_type=1
                  and sort>0
                  order by sort asc
                  limit {$limit}
                 " ;
            $temp = DbSelect::fetchAll($db, $sql, [ $this->id ]);
            foreach ( $temp as $v ) {
                $pic[]= [
                   'url' =>  \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $v['url']),
                   
                ];
            }
        }
        $new['pic'] = $pic;
        
        // 点击量
        $redis = Sys::get_container_redis();
        $key = "news:click:".$this->id;
        $count = $redis->get($key);
        $new['click_count'] = intval($count) + $this->click_count;
        
        return $new;
        
    }

     
    
}
