<?php
namespace BBExtend\user\achievement;

/**
 * 
 * 
 * User: 谢烨
 */

interface UpdateInterface
{
    /**
     * 更新成就
     * @param int $param 这是增量，注意： 
     */
    public function update_ach($param);
    
    
    public function get_event();
}