<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
use BBExtend\Sys;

/**
 * app底部导航规则表
 * 
 * User: 谢烨
 */
class BottomBar extends Model 
{
    protected $table = 'bb_bottom_bar_rule';
    protected $primaryKey="id";
    
    public $timestamps = false;
    
    public static function get_rule_id()
    {
        $time = time();
        $db  = Sys::get_container_dbreadonly();
        $sql = "select * from bb_bottom_bar_rule where zip_path != '' and is_valid=1 order by id desc";
        $result = $db->fetchAll($sql);
       
        $rule =0;
        foreach ( $result as $v ) {
           $boo = true;
           if ( $v['start_time'] && $v['start_time'] > $time ) {
               $boo = false;
           }
           if ( $v['end_time'] && $v['end_time'] < $time ) {
               $boo = false;
           }
           if ($boo) {
               $rule = $v['id'];
               break;
           }
           
           
        }
        if (!$rule) {
            $rule=1;
        }
        
        return $rule;
    }
    
    public static function get_pics_lists($rule_id)
    {
        $db     = Sys::get_container_dbreadonly();
        $sql    = "select * from bb_bottom_bar_pic where rule_id = ?";
        $result = $db->fetchAll($sql,[ $rule_id ]);
        $arr = $ios_arr =  [
                'show'=>[],
                'live'=>[],
                'my'=>[],
                'friend'=>[],
                'activity'=>[],
                
        ];
        foreach ($arr as $key =>$v) {
            foreach ( $result as $db_v ) {
                if ($db_v['pic_key'] == $key ) {
                    $arr[$key]['color_2x_url'] = $db_v['color_2x_url'];
                    $arr[$key]['color_3x_url'] = $db_v['color_3x_url'];
                    $arr[$key]['gray_2x_url']  = $db_v['gray_2x_url'];
                    $arr[$key]['gray_3x_url']  = $db_v['gray_3x_url'];
                    
                    $ios_arr[$key]['color_2x_url'] =  self::get_file_name( $db_v['color_2x_url']);
                    $ios_arr[$key]['color_3x_url'] = self::get_file_name($db_v['color_3x_url']);
                    $ios_arr[$key]['gray_2x_url']  = self::get_file_name($db_v['gray_2x_url']);
                    $ios_arr[$key]['gray_3x_url']  = self::get_file_name($db_v['gray_3x_url']);
                }
            }
        }
        
        return ['android' =>$arr, 'ios'=>$ios_arr ];
        
    }
    
    private static function get_file_name($name){
        return preg_replace('#^.+?/([^/]+)$#', '$1', $name);
    }
    
    
    
    public static function get_pics_for_ios($rule_id)
    {
        $db     = Sys::get_container_dbreadonly();
        $sql    = "select * from bb_bottom_bar_rule where id = ?";
        $result = $db->fetchRow($sql,[ $rule_id ]);
        
        $temp = self::get_pics_lists($rule_id);
        $result['all_pic'] = $temp['ios'];
        
        return $result;
        
    }
    
    
//     }
}



