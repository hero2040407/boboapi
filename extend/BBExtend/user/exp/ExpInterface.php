<?php
namespace BBExtend\user\exp;

/**
 * 经验值类，新的，老的Level逐渐废止。
 * 
 * 谢烨 2016 12
 */

// use BBExtend\Sys;
// use think\Db;


abstract  class ExpInterface
{
    abstract  function add_exp(Exp $exp);
}