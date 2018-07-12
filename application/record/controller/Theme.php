<?php
namespace app\record\controller;


use BBExtend\Sys;


/**
 * 器
 * 
 * @author xieye
 *
 */
class Theme
{
    /**
     * 列表
     * 
     * //返回最新，最热
     * 
     * @return number[]|string[]|string[]|number[]
     */
    public function index($uid)
    {
        $db = Sys::get_container_dbreadonly();
        $count = 10;
        $sql ="select * from bb_theme 
where is_valid=1
order by use_count desc limit 10";
        $result1 = $db->fetchAll($sql);
        $list_hot = $this->filter($result1);
        
        
//         $sql ="select * from bb_theme order by last_use_time desc limit 10";
//         $result2 = $db->fetchAll($sql);
//         $list_recent = $this->filter($result2);
        
        $sql="select theme_id,theme_title from bb_record where uid=? 
 and audit=1
 and exists(
  select 1 from bb_theme
   where bb_theme.id = bb_record.theme_id
    and  bb_theme.is_valid=1
)
order by id desc limit 50";
        $result2 = $db->fetchAll($sql,[ $uid ]);
        $list_recent = $this->filter2($result2);
        
        
        return [
                'code'=>1,
                'data'=>[
                        'list_hot'=>$list_hot,
                        'list_recent'=>$list_recent,
                ]
                
        ];
        
    }
    
    private function filter($arr)
    {
        $new=[];
        foreach ($arr as $v) {
            $temp = [];
            $temp['id'] =$v['id'];
            $temp['theme_title']=$v['title'];
            $new[]= $temp;
        }
        return $new;
    }
    
    private function filter2($arr)
    {
        $temp_ids=[];
        
        $new=[];
        $i=0;
        foreach ($arr as $v) {
            
            
            
            if (in_array( $v['theme_id'], $temp_ids )) {
                
            }else {
                if ( $v['theme_title'] ) {
                    $temp_ids[]= $v['theme_id'];
                    
                    $temp = [];
                    $temp['id'] =$v['theme_id'];
                    $temp['theme_title']=$v['theme_title'];
                    $new[]= $temp;
                    
                    $i++;
                    if ($i>=10) {
                        break;
                    }
                }
            }
        }
        return $new;
    }
    
    
}