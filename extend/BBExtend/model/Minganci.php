<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
use BBExtend\Sys;
/**
 * 关的类
 * User: 谢烨
 */
class Minganci extends Model
{

    protected $table = 'bb_minganci';

    public $timestamps = false;
    
    private $_err_message='';// 错误信息
    

    public function get_all_list ( )
    {
        
        $redis = \BBExtend\Sys::get_container_redis( );
        $key = 'minganci_list:all';
        $list = $redis->lRange( $key, 0, - 1 );
        if (empty( $list )) {
            
            $db = \BBExtend\Sys::get_container_dbreadonly( );
            $sql = "select name from bb_minganci order by id";
            $query = $db->query( $sql );
            while ($row = $query->fetch( )) {
                $redis->rPush( $key, $row['name'] );
            }
            
            
            $list = $redis->lRange( $key, 0, - 1 );
            // 设置延时消失。
            $redis->setTimeout($key, 1* 3600);
          // echo 123;
        }
        $list = (array) $list;
        return $list;
    }

    /**
     * 星号过滤
     * 
     * @param unknown $name
     * @return string
     */
    public function filter_by_asterisk ( $name )
    {
        // 这里有两种策略可选，百度还是 自己过滤。
        return $this->filter_baidu($name);
    
    }
    
    
    /**
     * 自己过滤
     *
     * @param unknown $name
     * @return string
     */
    private function filter_myself ( $name )
    {
        $name = strval($name);
        $list = $this->get_all_list();
        foreach ( $list as $v ) {
           // $v2 = str_replace('#', '', $v);
            if ($v) {
                if ( strpos( $name, $v ) !== false) {
                    $name = str_replace($v, '***', $name);
                    break;
                }
            }
        }
        return $name;
        
    }
    
    
    /**
     * 百度过滤
     * 
     * @param unknown $name
     * @return string
     */
    private function filter_baidu ( $name )
    {
        $name = strval($name);
        if (empty( $name )) {
            return $name;
        }
        
        $url ="https://aip.baidubce.com/rest/2.0/antispam/v2/spam?access_token=". Sys::get_baidu_token() ;
        $data=[
                'content'=>$name,
        ];
        $response = \Requests::post( $url ,['Content-Type'=>'application/x-www-form-urlencoded' ], $data);
        $json_str= $response->body ;
        $arr = json_decode($json_str,1);
        
        // 万一发生未知异常，例如超出10000次限额访问。
        if (isset( $arr['error_code'] )    ){
            return $name;
        }
        
        $spam = $arr['result']['spam'];
        if ( $spam==0 ) { // 0表示正常，无违禁词语。
            return $name;
        }
        $temp=[];
        if ($spam==1 || $spam==2  ) {
           
            foreach( $arr['result']['reject'] as $v ) {
                $temp =  array_merge( $temp,  $v['hit'] );
            }
            foreach( $arr['result']['review'] as $v ) {
                $temp =  array_merge( $temp,  $v['hit'] );
            }
        }
        
        foreach ( $temp as $v ) {
            if ($v) {
              $name = str_replace($v, '***', $name);
            }
        }
        return $name;
        
    }
    
    
}
