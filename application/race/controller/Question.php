<?php
namespace app\race\controller;
// use BBExtend\BBShop;
use think\Controller;
use BBExtend\Sys;
/**
 * 
 * 大赛
 * 
 * 2017 03
 * User: xieye
 */
class Question extends Controller
{
    /**
     * 
     * 大赛提问,
     * 
     * @return number[]|string[]|number[]|string[][]|boolean[][]|number[][]|mixed[][]
     */
    public function add($type=2, $title='',$ds_id=0,$question_uid=0  )
    {
        $db = Sys::get_container_db();
        $db->insert('ds_question', [
            'question_uid' =>intval($question_uid),
            'ds_id' =>intval($ds_id),
            'question' => $title,
            'type'  => intval($type),
            'question_time' =>time(),
        ]);
        return ['code'=>1];
    }
    
    /**
     * 回答，注意，本接口只支持post
     * @param unknown $ds_id
     */
    public function answer()
    {
        $ds_id = intval($_POST['ds_id']);
        $db = Sys::get_container_db();
        $db->update('ds_question', [
            'answer_time' =>time(),
            'answer' => $_POST['content'],
            
        ], 'ds_id='.$ds_id);
        return ['code'=>1,];
    }
    
    
   
    
}