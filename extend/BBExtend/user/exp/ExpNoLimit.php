<?php
namespace BBExtend\user\exp;

/**
 * 经验值类，新的，老的Level逐渐废止。
 * 
 * 谢烨 2016 12
 */

// use BBExtend\Sys;
use think\Db;

class ExpNoLimit extends ExpInterface
{
    public function add_exp(Exp $exp)
    {
        $uid = $exp->uid;
        $typeint = $exp->typeint;
        $who_uid = $exp->who_uid;
        
        // 没限制最简单。
        return $exp->update();
        
    }

}