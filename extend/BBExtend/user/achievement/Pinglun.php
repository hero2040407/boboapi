<?php
namespace BBExtend\user\achievement;

/**
 * 
 * 评论更新成就
 * User: 谢烨
 */

class Pinglun extends Ach implements UpdateInterface
{
    public function get_event()
    {
        return 'pinglun';
    }
    
    /**
     * 
     * @param int $param 评论次数增量
     * @see \BBExtend\user\achievement\Ach::update()
     */
    public function update_ach($param)
    {
        if ($param==0) {
            return;
        }
        $param=intval($param);
        
        $old_ach = $this->ach->pinglun;
        
        //不管怎样，需要更新汇总表
        $ach_obj = $this->ach;
        $summary_obj = $this->ach_summary;
        $summary_obj->pinglun += $param;
        $summary_obj->save();
        
        // 根据现在的level，来判断是否给成就表更新数据。
        $new_ach = self::jisuan($summary_obj->pinglun);
        
        if ($old_ach!= $new_ach) {
            $ach_obj->pinglun = $new_ach;
            $ach_obj->save();
            $this->old = $old_ach;
            $this->new = $new_ach;
            return true;
        }
        
    }
    
    public static function jisuan($zhibo)
    {
         if ($zhibo <50) {
                $result = 0;
            }elseif ($zhibo < 200) {
                $result = 1;
            }elseif ($zhibo < 500) {
                $result = 2;
            }else {
                $result = 3;
            }
        return $result;
    }
    
    public function get_bonus_value($new_level) {
        switch ($new_level) {
            case 1:
                return 30;
            case 2:
                return 60;
            case 3:
                return 100;
        }
        return 0;
    }
    
}