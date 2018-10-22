<?php
namespace app\backstage\controller;

use app\backstage\service\SetRaceStatus;
use BBExtend\backmodel\RaceField;
use BBExtend\backmodel\RaceRegistration;
use BBExtend\Sys;
use BBExtend\DbSelect;
use think\cache\driver\File;


class Race  extends Common
{
    
    public function detail($ds_id) 
    {
        
        $race = \BBExtend\backmodel\Race::find( $ds_id );
        if (!$race) {
            return ['code'=>400,'message' =>' 大赛id错误' ];
        }
        
        return ['code' =>1,'data'=>$race->detail() ];
    }
    
    
    public function edit_valid($ds_id = 0, $is_valid) {
        $db = Sys::get_container_db_eloquent();
        $group = (new File())->get($ds_id.'age_group');
        if (!$group) $this->error('请先设置分组');
        $count = (new \BBExtend\backmodel\RaceField())->where('race_id',$ds_id)->count();
        if (!$count) $this->error('请先添加赛区');
       // Sys::debugxieye($is_valid);
        $is_valid = $is_valid ? 1 : 0 ;
        //Sys::debugxieye($is_valid);
        $sql="select count(*) from ds_race where id = ? ";
        $result = DbSelect::fetchOne($db, $sql,[ $ds_id ]);
        if (!$result) {
            return ['code'=>400,'message'=>'id错误'];
        }
        
        $db::table('ds_race')->where('id' , $ds_id )->update(['is_active'=>$is_valid]);
        return ['code'=>1, ];
    }
    
    
    /**
     * 未过期大赛
     * 
     * @return number[]|string[]|number[]
     */
    public function index($is_active=null,$per_page=10,$page=1,$proxy_id=null,$ds_id=null) 
    {
        $time = time();
     //   $proxy_id = input("get.proxy_id") ;
        $map = [
            'parent' => 0,
            'delete_time' => null
        ];
        $db = Sys::get_container_db_eloquent();
        $paginator = $db::table('ds_race')->select(['id',]);
        $paginator =  $paginator->where($map);// 确保只查大赛
        
        if ($is_active != null ) {
            $paginator =  $paginator->where( "is_active",$is_active );
            $paginator =  $paginator->where( "end_time",">", time() );
        }
        //Sys::debugxieye($proxy_id."!2!");
        //Sys::debugxieye("param2". input("param.proxy_id") );
        if ($proxy_id != null ) {
          //  Sys::debugxieye($proxy_id."!!");
            $paginator =  $paginator->where( "proxy_id",$proxy_id );
        }
        if ($ds_id != null ) {
            $paginator =  $paginator->where( "id",$ds_id );
        }
       
    //    order by has_end asc, sort desc , start_time desc
        
        $paginator = $paginator
           ->orderBy('has_end', 'asc')
           ->orderBy('sort', 'desc')
           ->orderBy('start_time', 'desc')
           ->paginate($per_page, ['*'],'page',$page);
        $result=[];
        foreach ($paginator as $v) {
            $result[]= $v->id;
        }  
        
       
        $new=[];
        foreach ($result as $v) {
            $temp = \BBExtend\backmodel\Race::find( $v );
            $new[]= $temp->display();
        }
        return ['code'=>1, 'data'=>['list' => $new, 
                'pageinfo' =>$this->get_pageinfo($paginator, $per_page) ] ];
    }
    
  
    /**
     * 代理商的大赛
     *
     * @return number[]|string[]|number[]
     */
    public function my_list()
    {
        $db = Sys::get_container_db_eloquent();
        
        $time = time();
        
        $sql="
               select * from ds_race
                where is_active=1 and parent=0
                and   end_time > {$time}
                order by id desc
";
        $result = DbSelect::fetchAll($db, $sql  );
        $new=[];
        foreach ($result as $v) {
            $new[]= [
                    'id'=> $v['id'],
                    'title' => $v['title'],
            ];
        }
        return ['code'=>1, 'data'=>['list' => $new ] ];
        //  $db::table('backstage_auth')->where('id' , $id )->update(['is_valid'=>0]);
        //  return ['code'=>1, ];
    }
    
    /**
     * 大赛新增
     * 
     * @param unknown $id
     * @param unknown $proxy_id
     * @param unknown $register_start_time
     * @param unknown $register_end_time
     * @param unknown $start_time
     * @param unknown $end_time
     * @param unknown $is_active
     * @param unknown $title
     * @param unknown $banner
     * @param unknown $uid
     * @return 
     */
    public function add( $proxy_id,$register_start_time, $register_end_time,
            $start_time, $end_time,$title,$banner,$uid,$summary,$detail,
            $min_age=0, $max_age=0,$reward='',$online_type=2,
            $has_group=0,$group_code='',$group_title='',
            $group_content='',$group_pic='',$group_qrcode_pic='',
            $group_or_person=1,$money=0,$upload_type=1,$prize = '')
    {
        
        $admin = \BBExtend\model\BackstageAdmin::find( $proxy_id );
        if (!$admin) {
            return ['code'=>400, 'message'=> '代理ID不存在' ];
        }
        if ( $admin->is_valid==0 || $admin->level != 1 ) {
            return ['code'=>400, 'message'=> '代理ID失效，或级别错误，不是代理账号' ];
        }
        
        $user = \BBExtend\model\User::find( $uid );
        if (!$user) {
            return ['code'=>400, 'message'=> 'uid错误' ];
        }
        if (!in_array( $online_type,[1,2] )) {
            return ['code'=>400, 'message'=> 'online_type错误' ];
        }

       $race =new \BBExtend\backmodel\Race();
       $race->prize = $prize;
       $race->title = $title;
       $race->is_active=0;
       $race->proxy_id = $proxy_id;
       $race->register_start_time = intval( $register_start_time );
       $race->register_end_time = intval( $register_end_time );
       $race->start_time = intval( $start_time );
       $race->end_time = intval( $end_time );
       $race->uid = intval( $uid );
       $race->banner_bignew = strval( $banner );
       $race->summary = strval( $summary );
       $race->detail = strval( $detail );
       $race->min_age = intval( $min_age );
       $race->max_age = intval( $max_age );
       $race->reward = strval( $reward );
       $race->online_type = $online_type;
       $race->money = floatval( $money );
       $race->upload_type = $upload_type;
       $race->save();
        
        
        $group_id=0;
        if ($has_group) {
            
            $group = new \BBExtend\backmodel\Group();
            $group->type=2;// 大赛只能qq，type=2
            $group->code=  $group_code;// 大赛只能qq，type=2
            $group->bb_type = 2;// 大赛2
            $group->ds_id=$race->id ;// 
            $group->title = $group_title ;
            $group->pic = $group_pic;// 展示图
            $group->qrcode_pic = $group_qrcode_pic ;// 大赛只能qq，type=2
            $group->create_time = time();// 大赛只能qq，type=2
            $group->summary = $group_content;
            $group->group_or_person = $group_or_person;
            $group->save();
        }

        $this->adminActionLog('新增了大赛,id为'.$race->id);
        return ['code'=>1, 'data'=>['insert_id' => $race->id ] ];
        
    }
    
    
    public function edit($id, $proxy_id,$register_start_time, $register_end_time,
            $start_time, $end_time,$is_active,$title,$banner,$uid,$summary,$detail,
            $min_age=0, $max_age=0,$reward='',$online_type=2,
            $has_group=0,$group_code='',$group_title='',
            $group_content='',$group_pic='',$group_qrcode_pic='',
            $group_or_person=1 , $money=0,$upload_type=1,$reward='',$prize = ''
            )
    {
  //      Sys::display_all_error();
        
        $race = \BBExtend\model\Race::find( $id );
        if (!$race) {
            return ['code'=>400, 'message'=> 'race_id错误' ];
        }
        if ($race->level != 1  ) {
            return ['code'=>400, 'message' => 'race_id权限错误' ];
        }
        
        if (!in_array( $online_type,[1,2] )) {
            return ['code'=>400, 'message'=> 'online_type错误' ];
        }
        
        
        $admin = \BBExtend\model\BackstageAdmin::find( $proxy_id );
        if (!$admin) {
            return ['code'=>400, 'message'=> '代理ID不存在' ];
        }
        if ( $admin->is_valid==0 || $admin->level != 1 ) {
            return ['code'=>400, 'message'=> '代理ID失效，或级别错误，不是代理账号' ];
        }
        
        $user = \BBExtend\model\User::find( $uid );
        if (!$user) {
            return ['code'=>400, 'message'=> 'uid错误' ];
        }
        
        if ($this->get_userinfo_role()=='admin') {
           $race->is_active = $is_active?1:0;
        }
        
        $race->proxy_id = $proxy_id;
        $race->register_start_time = intval( $register_start_time );
        $race->register_end_time = intval( $register_end_time );
        $race->start_time = intval( $start_time );
        $race->end_time = intval( $end_time );
        $race->prize = $prize;

        $race->uid = intval( $uid );
        $race->title = strval( $title );
        $race->banner_bignew = strval( $banner );
        $race->summary = strval( $summary );
        $race->detail = strval( $detail );
        $race->min_age = intval( $min_age );
        $race->max_age = intval( $max_age );
        $race->reward = strval( $reward );
        $race->online_type = $online_type;
        $race->money = floatval( $money );
        $race->upload_type = $upload_type;
        $race->reward = $reward;
        
        $race->save();
        
        // 不管咋样，先删除 群信息。
        $db = Sys::get_container_db();
        $sql="delete from bb_group where bb_type=2 and ds_id=?";
        $db->query($sql,[ $id ]);
        if ($has_group) {
            
            $group = new \BBExtend\backmodel\Group();
            $group->type=2;// 大赛只能qq，type=2
            $group->code=  $group_code;// 大赛只能qq，type=2
            $group->bb_type = 2;// 大赛2
            $group->ds_id=$race->id ;//
            $group->title = $group_title ;
            $group->pic = $group_pic;// 展示图
            $group->qrcode_pic = $group_qrcode_pic ;// 大赛只能qq，type=2
            $group->create_time = time();// 大赛只能qq，type=2
            $group->summary = $group_content;
            $group->group_or_person = $group_or_person;
            $group->save();
        }
        $this->adminActionLog('修改了大赛,id为'.$race->id );
        return ['code'=>1 ];
    }
    
    
   /**
    * 大赛轮播图
    */
    public function edit_slide_show($ds_id, $list)
    {
      //  Sys::debugxieye('1:'.time() );
        
//         $json_str = file_get_contents("php://input");
//         $json_data = \BBExtend\common\Json::decode($json_str);
        
      //  $ds_id = $json_data['ds_id'];
        //Sys::debugxieye('2:'.time() );
      //  Sys::debugxieye("edit");
        
        $db = Sys::get_container_db_eloquent();
        $db::table("ds_lunbo")->where('ds_id', $ds_id)->delete();
        $pic_arr =  \BBExtend\common\Json::decode($list);
      //  Sys::debugxieye($pic_arr);
        
        foreach ($pic_arr as $v) {
            $v2 = $v;
            $v2['ds_id'] = $ds_id;
            $db::table("ds_lunbo")->insert($v2);
            
        }
        //Sys::debugxieye( '3:'. time() );
        
        return ['code'=>1];
    }

    /**
     * Notes:开启总决赛
     * Date: 2018/9/19 0019
     * Time: 下午 1:34
     * @throws
     */
    public function startFinal($race_id = '')
    {
        if (empty($race_id))
            $this->error('race_id必须');
        $register = new RaceRegistration();
        $res = (new RaceField())->where('race_id',$race_id)
            ->whereIn('status',[Field::SIGN_IN,Field::MATCH])->value('id');

        if($res) $this->error('还有赛区的比赛未完成');

        $count = (new RaceField())->where('race_id',$race_id)
            ->where('status',Field::STOP)->count();
        if ($count > 1) $this->error('请先将非总决赛的赛区设为结束状态');

        $area_id = (new RaceField())->where('race_id',$race_id)
            ->where('status',Field::STOP)->value('id');

        if (!$area_id) $this->error('请添加一个总决赛赛区并将其设为等待状态');

        $res = $register->where([
            'zong_ds_id' => $race_id,
            'race_status' => SetRaceStatus::ADVANCE
        ])->update([
            'ds_id' => $area_id,
            'race_status' => SetRaceStatus::SING_UP
        ]);

        if ($res) $this->success('大赛开启成功');
        $this->error('大赛开启失败');
    }
}




