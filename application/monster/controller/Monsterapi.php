<?php
namespace app\monster\controller;
use app\shop\model\Users;
use app\user\controller\User;
use BBExtend\BBMonster;
use BBExtend\BBRedis;
use BBExtend\Focus;
use think\Db;
class Monsterapi extends BBMonster
{

    public function __construct()
    {
        return NULL;
    }
    //获取某个怪兽的动画列表
    //获取表情配置
    //uid --人物id
    public function get_mostereanimationwithuid()
    {
        $uid = input('?param.uid')?input('param.uid'):0;
        $usercode=\app\user\model\Exists::userhExists($uid);
        if ($usercode!=1){
            return ['code'=>$usercode,'message'=>'error!'];
        }
        $UserMonsterDB_Array = self::get_monsterDB_by_uid($uid);
        $Data = array();
        $Data['monster_list'] = array();
        foreach ($UserMonsterDB_Array as $UserMonsterDB)
        {
            $Monster_Ani_Array = Db::table('bb_monster_animation')->where('monster_id',$UserMonsterDB['monster_id'])->select();
            $Level = $this->get_user_monster_level($UserMonsterDB);
            $AniDB = array();
            $AniDB['monster'] = array();
            $AniDB['monster']['monster_id'] = $UserMonsterDB['monster_id'];
            $AniDB['monster']['icon'] = \BBExtend\common\BBConfig::get_server_url().(self::get_monster_by_id($UserMonsterDB['monster_id'])['icon']);
            $AniDB['monster']['ani_list'] = array();
            foreach ($Monster_Ani_Array as $MonsterAniDB)
            {
                $MonsterDB = array();

                $MonsterDB['id'] = (int)$MonsterAniDB['id'];
                $MonsterDB['monster_id'] = (int)$MonsterAniDB['monster_id'];
                $MonsterDB['level'] = (int)$MonsterAniDB['level'];
                $MonsterDB['icon'] = \BBExtend\common\BBConfig::get_server_url().$MonsterAniDB['icon'];
                if ($MonsterAniDB['level'] <= $Level)
                {
                    $MonsterDB['is_lock'] = false;
                }else
                {
                    $MonsterDB['is_lock'] = true;
                }
                array_push($AniDB['monster']['ani_list'],$MonsterDB);
            }
            array_push($Data['monster_list'] ,$AniDB);
        }
        return ['data'=>$Data,'code'=>1,'message'=>'success!'];
    }

    //获取某个怪兽的动画列表
    //获取表情配置
    //monster_id --怪物ID
    public function get_mostereanimationwithmonsterid()
    {
        $uid = input('?param.uid')?input('param.uid'):0;
        $monster_id = input('?param.monster_id')?input('param.monster_id'):0;
        $usercode=\app\user\model\Exists::userhExists($uid);
        if ($usercode!=1){
            return ['code'=>$usercode,'message'=>'error!'];
        }
        if ($monster_id==0)
        {
            return ['data'=>"",'code'=>1,'message'=>'error!'];
        }
        $Data = array();
        $UserMonsterDB = self::get_user_monster_by_id($uid,$monster_id);
        if (!$UserMonsterDB)
        {
            return ['message'=>'您没有这个怪物','code'=>0];
        }
        $Level = $this->get_user_monster_level($UserMonsterDB);
        $MonsterAni_Array = Db::table('bb_monster_animation')->where('monster_id',$monster_id)->select();
        foreach ($MonsterAni_Array as $MonsterAni_DB)
        {
            $AniDB = array();
            $AniDB['id'] = (int)$MonsterAni_DB['id'];
            $AniDB['icon'] = \BBExtend\common\BBConfig::get_server_url().$MonsterAni_DB['icon'];
            $AniDB['level'] = (int)$MonsterAni_DB['level'];
            if ($AniDB['level'] <= $Level)
            {
                $AniDB['is_lock'] = false;
            }else
            {
                $AniDB['is_lock'] = true;
            }
            //$AniDB['animation_name'] = \BBExtend\common\BBConfig::get_server_url().$MonsterAni_DB['animation_name'];
            array_push($Data,$AniDB);
        }
        return ['data'=>$Data,'code'=>1,'message'=>'success!'];
    }
    ///通过怪物动画id获取怪物动画
    public function get_monsteranimationwithID()
    {
        $id = input('?param.id')?input('param.id'):'';
        $animationData=$this->get_monster_ani_by_id($id);
        $ServerURL = \BBExtend\common\BBConfig::get_server_url();
        header('Location: '.$ServerURL.$animationData['animation_name']);
        exit();
    }
    //通过ID获得怪兽动画
    private function get_monster_ani_by_id($id)
    {
        $vid = $id.'ani';
        $AniDB = BBRedis::getInstance('monster')->hGetAll($vid);
        if (!$AniDB)
        {
            $AniDB = Db::table('bb_monster_animation')->where('id',$id)->find();
            if ($AniDB)
            {
                BBRedis::getInstance('monster')->hMset($vid,$AniDB);
            }
        }
        return $AniDB;
    }
    //获取用户怪兽列表
    public function get_usermonster(){
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $dataArray=self::get_monster_by_uid($uid);
        return ['data'=>$dataArray,'code'=>1,'message'=>'success!'];
    }
    //获取所有怪兽列表
    public function get_all_monster(){
        $Monster_array = Db::table('bb_monster_list')->select();
        $Data = array();
        foreach ($Monster_array as $MonsterDB)
        {
            $DataDB = array();
            $DataDB['id'] = (int)$MonsterDB['id'];
            $DataDB['icon'] = \BBExtend\common\BBConfig::get_server_url().$MonsterDB['icon'];
            $DataDB['pic_url'] = \BBExtend\common\BBConfig::get_server_url().$MonsterDB['pic_url'];
            $DataDB['name'] = $MonsterDB['name'];
            $DataDB['info'] = $MonsterDB['info'];
            array_push($Data,$DataDB);
        }
        return ['data'=>$Data,'code'=>1,'message'=>'success!'];
    }

    //消耗一个宠物蛋开启宠物
    public function get_open_monster(){
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        //谢烨，提前判断能否打蛋。
        $is_ok = self::add_currency($uid,CURRENCY_MONSTER,-1,'兑换小怪兽');
        if (!$is_ok)
        {
            return ['message'=>'您没有足够的可以兑换的怪兽蛋','code'=>0];
        }
        
        $UserLevel = User::get_user_level($uid);
        $Monster_Array = Db::table('bb_monster_list')->where(['level'=>['<=',$UserLevel]])->select();
        $Count = count($Monster_Array);
        $RandId = rand(0,$Count-1);
        $MonsterDB = self::get_monster_by_id($Monster_Array[$RandId]['id']);
        $UserMonsterDB = Db::table('bb_monster_data')->where(['uid'=>$uid,'monster_id'=>$Monster_Array[$RandId]['id']])->find();
        
        if ($MonsterDB&&!$UserMonsterDB)
        {
                $User_MonsterDB = array();
                $User_MonsterDB['uid'] = $uid;
                $User_MonsterDB['state'] = 1;
                //谢烨20160928，bug修改
                $User_MonsterDB['monster_id'] = intval($Monster_Array[$RandId]['id']);
                $User_MonsterDB['create_time'] = time();
                $User_MonsterDB['like'] = 0;
                $User_MonsterDB['exp'] = 0;
                $User_MonsterDB['level'] = 1;
                Db::table('bb_monster_data')->insert($User_MonsterDB);
                $monster_count = count(self::get_monster_by_uid($uid));
                Db::table('bb_users')->where('uid',$uid)->update(['monster_count'=>$monster_count]);
                BBRedis::getInstance('user')->hSet($uid,'monster_count',$monster_count);

                $DataDB = array();
                $DataDB['icon'] = \BBExtend\common\BBConfig::get_server_url().$MonsterDB['icon'];
                $DataDB['pic_url'] = \BBExtend\common\BBConfig::get_server_url().$MonsterDB['pic_url'];
                $DataDB['monsterName'] = $MonsterDB['name'];
                $DataDB['monsterInfo'] = $MonsterDB['info'];
                $DataDB['id'] = intval($MonsterDB['id']);
                $DataDB['author'] = $MonsterDB['author'];
                $DataDB['author_icon'] = \BBExtend\common\BBConfig::get_server_url().$MonsterDB['author_icon'];
                $DataDB['author_img'] = \BBExtend\common\BBConfig::get_server_url().$MonsterDB['author_img'];
                $DataDB['author_uid'] = (int)$MonsterDB['author_uid'];
                
                //谢烨 2016 10
                //对目标用户修改排名
                \BBExtend\user\Ranking::getinstance($uid)->set_guaishou_ranking();
                
                
                return ['data'=>$DataDB,'message'=>'恭喜你获得了一个新的小怪兽','code'=>1];
        }
        return ['message'=>'抱歉没有抽到小怪兽哦~请再接再厉','code'=>0];
    }
    //获取领养人列表
    public function get_adopt_monster_user_list(){
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $monster_id = input('?param.monster_id')?(int)input('param.monster_id'):0;
        $start_id = input('?param.startid')?input('param.startid'):0;
        $length = input('?param.length')?input('param.length'):20;
        $User_list = Db::table('bb_monster_data')->where(['monster_id'=>$monster_id])->order('create_time','desc')->limit($start_id,$length)->select();
        
    //    \Sys::debugxieye($User_list);
        $Data = array();
        foreach ($User_list as $UserDB)
        {
            $UserData = array();
             $UserData['pic'] = User::get_userpic($UserDB['uid']);
            $UserData['address'] = User::get_user_address($UserDB['uid']);
            $UserData['sex'] = User::get_usersex($UserDB['uid']);
            $UserData['nickname'] = User::get_nickname($UserDB['uid']);
            $UserData['is_focus'] = Focus::get_focus_state($uid,$UserDB['uid']);
            $UserData['uid'] = (int)$UserDB['uid'];
            
            
            //谢烨20160922，加vip返回字段
            try{
            $UserData['vip'] = \BBExtend\common\User::is_vip($UserData['uid']) ;
            }catch (\Exception $e) {
                $UserData['vip'] =0;
            }
            array_push($Data,$UserData);
        }
        if (count($User_list) == $length)
        {
            return ['data'=>$Data,'is_bottom'=>0,'code'=>1];
        }
        return ['data'=>$Data,'is_bottom'=>1,'code'=>1];
    }
    //获取怪兽领养人数量
    public function get_adopt_monster_count()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $monster_id = input('?param.monster_id')?(int)input('param.monster_id'):0;
        //谢烨20160930
        $Count = Db::table('bb_monster_data')->where(['monster_id'=>$monster_id])->count();
        $data = array();
        $data['count'] = $Count;
        $MonsterDB = self::get_monster_by_id($monster_id);
        $data['is_focus'] = Focus::get_focus_state($uid,$MonsterDB['author_uid']);
        return ['data'=>$data,'code'=>1];
    }
}
?>