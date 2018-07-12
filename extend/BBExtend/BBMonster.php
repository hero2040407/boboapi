<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/22
 * Time: 17:28
 */

namespace BBExtend;


use think\Db;

class BBMonster extends Currency
{
    public static $OneDaySecond = 86400;
//获得用户怪兽的等级
    public function get_user_monster_level($MonsterDB)
    {
        $NowTime=time();
        $create_time = $MonsterDB['create_time'];
        $Level = (int)(($NowTime - $create_time) / self::$OneDaySecond);
        if ($Level<1)
        {
            $Level = 1;
        }
        return $Level;
    }
    //获得所有怪兽根据UID
    public static function get_usermonsterbyuser($uid)
    {
        $Monster_List_DB = Db::table('bb_monster_data')->where('uid',$uid)->select();
        $MonsterArray = array();
        foreach ($Monster_List_DB as $MonsterDB)
        {
            $create_time = $MonsterDB['create_time'];
            $NowTime=time();
            if ($NowTime>$create_time)
            {
                $Level = (int)(($NowTime - $create_time) / self::$OneDaySecond);
                $MonsterDB['level'] = $Level;
            }
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            $MonsterDB['pic_url']=$ServerURL.$MonsterDB['pic_url'];
            $MonsterDB['eggpic_url']=$ServerURL.$MonsterDB['eggpic_url'];
            array_push($MonsterArray,$MonsterDB);
        }
        return $MonsterArray;
    }

    //新人物进游戏得到的宠物蛋
    public static function get_new_monster($uid)
    {
        $monster_id = rand(1,3);
        $MonsterDB = self::get_monster_by_id($monster_id);
        $User_MonsterDB = array();
        $User_MonsterDB['uid'] = $uid;
        $User_MonsterDB['state'] = 1;
        $User_MonsterDB['monster_id'] = $monster_id;
        $User_MonsterDB['create_time'] = time();
        $User_MonsterDB['like'] = 0;
        $User_MonsterDB['exp'] = 0;
        $User_MonsterDB['level'] = 1;
        Db::table('bb_monster_data')->insert($User_MonsterDB);

        $ServerURL = \BBExtend\common\BBConfig::get_server_url();
        $UserMonsterDB_list = array();
        $UserMonsterDB = array();
        $UserMonsterDB['id'] = $monster_id;
        $UserMonsterDB['monster_id'] = $monster_id;
        $UserMonsterDB['create_time'] = time();
        $UserMonsterDB['monsterName'] = $MonsterDB['name'];
        $UserMonsterDB['level'] = 1;
        $UserMonsterDB['monsterInfo'] = $MonsterDB['info'];
        $UserMonsterDB['eggpic_url'] =$ServerURL.$MonsterDB['eggpic_url'];
        $UserMonsterDB['pic_url'] = $ServerURL.$MonsterDB['pic_url'];
        $UserMonsterDB['author'] = $MonsterDB['author'];//作者名称
        $UserMonsterDB['author_icon'] = $ServerURL.$MonsterDB['author_icon'];//作者头像地址
        $UserMonsterDB['author_img'] = $ServerURL.$MonsterDB['author_img'];//作者原画地址
        $UserMonsterDB['author_uid'] = (int)$MonsterDB['author_uid'];
        $UserMonsterDB['like'] = (int)$MonsterDB['like'];//作者原画地址
        array_push($UserMonsterDB_list,$UserMonsterDB);
        
        //谢烨 2016 10
        //对目标用户修改排名
       // \BBExtend\user\Ranking::getinstance($uid)->set_guaishou_ranking();
        
        return $UserMonsterDB_list;
    }
    public static function get_monster_list($uid)
    {
        return self::get_monster_by_uid($uid);
    }
    //通过UID获得用户所有怪兽SQL元数据
    public static function get_monsterDB_by_uid($uid)
    {
        $MonsterDB_Array = Db::table('bb_monster_data')->where('uid',$uid)->select();
        if (!$MonsterDB_Array)
        {
            return self::get_new_monster($uid);
        }
        return $MonsterDB_Array;
    }
    //通过UID跟怪兽ID获取某个属于这个UID的怪兽
    public static function get_user_monster_by_id($uid,$monster_id)
    {
        $MonsterDB = Db::table('bb_monster_data')->where(['uid'=>$uid,'monster_id'=>$monster_id])->find();
        return $MonsterDB;
    }
    //通过UID获得用户所有怪兽
    public static function get_monster_by_uid($uid)
    {
        $MonsterDB_Array = Db::table('bb_monster_data')->where('uid',$uid)->select();
        if (!$MonsterDB_Array)
        {
            return self::get_new_monster($uid);
        }
        $Monster_List = array();
        foreach ($MonsterDB_Array as $MonsterDB)
        {
            $MonsterCfgDB = self::get_monster_by_id($MonsterDB['monster_id']);
            $UserMonsterDB = array();
            $UserMonsterDB['id'] = (int)$MonsterDB['monster_id'];
            $UserMonsterDB['monsterName'] = $MonsterCfgDB['name'];
            $create_time = $MonsterDB['create_time'];
            $NowTime=time();
            if ($NowTime>$create_time)
            {
                $Level = (int)(($NowTime - $create_time) / self::$OneDaySecond);
                $UserMonsterDB['level'] = $Level;
            }
            $UserMonsterDB['monsterInfo'] = $MonsterCfgDB['info'];
            $UserMonsterDB['eggpic_url'] =  \BBExtend\common\BBConfig::get_server_url().$MonsterCfgDB['eggpic_url'];
            $UserMonsterDB['pic_url'] = \BBExtend\common\BBConfig::get_server_url().$MonsterCfgDB['pic_url'];
            $UserMonsterDB['author_uid'] = (int)$MonsterCfgDB['author_uid'];
            $UserMonsterDB['author'] = $MonsterCfgDB['author'];
            $UserMonsterDB['author_icon'] = \BBExtend\common\BBConfig::get_server_url().$MonsterCfgDB['author_icon'];
            $UserMonsterDB['author_img'] = \BBExtend\common\BBConfig::get_server_url().$MonsterCfgDB['author_img'];
            $UserMonsterDB['like'] = (int)$MonsterCfgDB['like'];
            
            // xieye 2016 10 25
            $UserMonsterDB['vip'] = \BBExtend\common\User::is_vip($MonsterCfgDB['author_uid']) ;
            
            array_push($Monster_List,$UserMonsterDB);
        }
        return $Monster_List;
    }
    //通过ID获得怪兽数据
    public static function get_monster_by_id($id)
    {
        $vid = $id.'monster';

        $MonsterDB = BBRedis::getInstance('monster')->hGetAll($vid);
        if (!$MonsterDB)
        {
            $MonsterDB = Db::table('bb_monster_list')->where('id',$id)->find();
            BBRedis::getInstance('monster')->hMset($vid,$MonsterDB);
        }
        return $MonsterDB;
    }
}