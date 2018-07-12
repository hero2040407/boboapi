<?php
namespace app\apptest\controller;

use BBExtend\BBRedis;
use  think\Db;
use BBExtend\Sys;
use BBExtend\BBUser;

class Present
{
    public function create_data()
    {
        $db = Sys::get_container_db();
        $sql='delete from bb_present';
        $db->query($sql);
        
        $db->insert("bb_present", [
            'id'   =>1,
            'title'=>'美味的棒棒糖',
            'pic'=>'/public/pic/present/img_liwu_1@2x.png',
            'gold'=>'5',
            'experience'=>'10',
        ]);
        $db->insert("bb_present", [
            'id'   =>2,
            'title'=>'彩虹汽球',
            'pic'=>'/public/pic/present/img_liwu_2@2x.png',
            'gold'=>'10',
            'experience'=>'20',
        ]);
        $db->insert("bb_present", [
            'id'   =>3,
            'title'=>'糖果巧克力',
            'pic'=>'/public/pic/present/img_liwu_3@2x.png',
            'gold'=>'30',
            'experience'=>'60',
        ]);
        $db->insert("bb_present", [
            'id'   =>4,
            'title'=>'杯子蛋糕',
            'pic'=>'/public/pic/present/img_liwu_4@2x.png',
            'gold'=>'50',
            'experience'=>'100',
        ]);
        $db->insert("bb_present", [
            'id'   =>5,
            'title'=>'玩具水枪',
            'pic'=>'/public/pic/present/img_liwu_5@2x.png',
            'gold'=>'60',
            'experience'=>'120',
        ]);
        $db->insert("bb_present", [
            'id'   =>6,
            'title'=>'小仙女魔棒',
            'pic'=>'/public/pic/present/img_liwu_6@2x.png',
            'gold'=>'100',
            'experience'=>'200',
        ]);
        $db->insert("bb_present", [
            'id'   =>7,
            'title'=>'天使翅膀',
            'pic'=>'/public/pic/present/img_liwu_7@2x.png',
            'gold'=>'100',
            'experience'=>'200',
        ]);
        $db->insert("bb_present", [
            'id'   =>8,
            'title'=>'动感篮球',
            'pic'=>'/public/pic/present/img_liwu_8@2x.png',
            'gold'=>'200',
            'experience'=>'400',
        ]);
        $db->insert("bb_present", [
            'id'   =>9,
            'title'=>'乐高小人',
            'pic'=>'/public/pic/present/img_liwu_9@2x.png',
            'gold'=>'300',
            'experience'=>'600',
        ]);
        $db->insert("bb_present", [
            'id'   =>10,
            'title'=>'变型金刚',
            'pic'=>'/public/pic/present/img_liwu_10@2x.png',
            'gold'=>'500',
            'experience'=>'1000',
        ]);
        $db->insert("bb_present", [
            'id'   =>11,
            'title'=>'奥特曼',
            'pic'=>'/public/pic/present/img_liwu_13@2x.png',
            'gold'=>'800',
            'experience'=>'1600',
        ]);
        $db->insert("bb_present", [
            'id'   =>12,
            'title'=>'芭比公主',
            'pic'=>'/public/pic/present/img_liwu_11@2x.png',
            'gold'=>'800',
            'experience'=>'1600',
        ]);
        $db->insert("bb_present", [
            'id'   =>13,
            'title'=>'遥控汽车',
            'pic'=>'/public/pic/present/img_liwu_12@2x.png',
            'gold'=>'1000',
            'experience'=>'2000',
        ]);
      
        
    }
}
