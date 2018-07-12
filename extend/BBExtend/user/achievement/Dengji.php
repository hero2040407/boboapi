<?php
namespace BBExtend\user\achievement;
use BBExtend\model\UserExp;

/**
 * 
 * 等级更新
 * User: 谢烨
 */

class Dengji extends Ach implements UpdateInterface
{
    public function get_event()
    {
        return 'dengji';
    }
    
    public function update_ach($param)
    { 
        if ($param==0) {
            return;
        }
        //等级无需更新汇总表
        $user_exp = UserExp::find($this->uid);
        // 根据现在的level，来判断是否给成就表更新数据。
        $old_ach = $this->ach->dengji;
        
        //计算新的等级
        $new_ach = self::jisuan($user_exp->level);
        
      
        if ($old_ach!= $new_ach) {
            $this->ach->dengji = $new_ach;
            $this->ach->save();
            $this->old = $old_ach;
            $this->new = $new_ach;
            return true;
        }
        
    }
    
    public static function jisuan($level)
    {
        if ($level <5) {
            $result = 0;
        }elseif ($level < 10) {
            $result = 1;
        }elseif ($level < 20) {
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