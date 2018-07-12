<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/18
 * Time: 15:00
 */

namespace app\user\controller;

//use think\Request;
//use think\Db;
use BBExtend\DbSelect;

use BBExtend\model\User;
use BBExtend\Sys;
use BBExtend\common\PicPrefixUrl;

class Vip
{
    /**
     * 通过付费方式获得vip，且还要填写个人信息资料。只有电话，无其他。
     * 
     * @param unknown $uid
     * @param unknown $token
     * @param string $name
     * @param string $phone
     * @param string $info
     * @return number[]|string[]|number[]
     */
    
    public function apply($uid,$token,$name='',$phone='', $info ='',$type=3,$pic='')
    {
        Sys::display_all_error();
        $db = Sys::get_container_db_eloquent();
       
        $name=trim( $name );
        $phone=trim( $phone );
        $info=strval( $info );
        
        if ( !$name || !$phone || !$info ) {
            return ['code'=>0, 'message'=>'信息不完整，请重新填写'];
        }
        
        $user = User::find($uid);
        if (!$user) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( !$user->check_token($token ) ) {
            return ['code'=>0,'message'=>'uid error'];
        }
        if ( $user->role !=1 ) {
            return ['code'=>0,'message'=>'role error'];
        }
        
        if ($type==3) {
            $sql="select count(*) from bb_vip_application_log where status=1 and uid= ?";
            $result = DbSelect::fetchOne($db, $sql,[ $uid ]);
            if (!$result) {
                return ['code'=>0,'message' =>'请先付费' ];
            }
            
            $sql="select count(*) from bb_vip_application_log where status=2 and uid= ?";
            $result = DbSelect::fetchOne($db, $sql,[ $uid ]);
            if ($result) {
                return ['code'=>0,'message' =>'您已提交过申请，不需重复提交' ];
            }
           
            
            $log = new \BBExtend\model\VipApplicationLog();
            $log->uid = $uid;
            $log->name = $name;
            $log->phone = $phone;
            $log->jianjie = $info;
            $log->status=2;
            $log->save();
        }
        $time = time();
        // 导师申请
        if ($type==2 ) {
           //前提，不能有未审核的，不能有审核成功的。 不能有最终激活，不能有填写个人资料
           $sql="select count(*) from bb_starmaker_application where uid=? and status=0";
           $count = DbSelect::fetchOne($db, $sql,[ $uid ]);
           if ($count) {
               return ['code'=>0,'message' =>'您已申请过，请耐心等待审核' ];
           }
           $sql="select count(*) from bb_starmaker_application where uid=? and status in (1,3,4)";
           $count = DbSelect::fetchOne($db, $sql,[ $uid ]);
           if ($count) {
               return ['code'=>0,'message' =>'您已申请过，无需再次申请' ];
           }
           
           $db::table('bb_starmaker_application')->where('uid', $uid)->delete();
           
           $db::table('bb_starmaker_application')->insert([
                   'create_time' => $time,
                   'lianxiren'  =>$name,
                   'phone'      => $phone,
                   'jianjie'    => $info,
                   'status' =>0,
                   'uid' =>$uid,
           ] );
        }
        
        
        // 机构申请
        if ($type==4 ) {
            //前提，不能有未审核的，不能有审核成功的。 不能有最终激活，不能有填写个人资料
            $sql="select count(*) from bb_brandshop_application where uid=? and status=0";
            $count = DbSelect::fetchOne($db, $sql,[ $uid ]);
            if ($count) {
                return ['code'=>0,'message' =>'您已申请过，请耐心等待审核' ];
            }
            $sql="select count(*) from bb_brandshop_application where uid=? and status in (1,3,4)";
            $count = DbSelect::fetchOne($db, $sql,[ $uid ]);
            if ($count) {
                return ['code'=>0,'message' =>'您已申请过，无需再次申请' ];
            }
            $db::table('bb_brandshop_application')->where('uid', $uid)->delete();
            $db::table('bb_brandshop_application')->insert([
                    'create_time' => $time,
                    'lianxiren'  =>$name,
                    'phone'      => $phone,
                    'jianjie'    => $info,
                    'pic'    => strval($pic),
                    'status' =>0,
                    'uid' =>$uid,
            ] );
        }
        
        return ['code'=>1,'message'=>'提交成功'];
        
    }
    
    
    
    
    
    
    
    
    
    /**
     * 6个条件页面
     * 
     * 假如我已经是童星，则status=0，不显示按钮。
     * 
     *  假如不是童星
     *    假如6个条件满足，
     *       假如未更新过个人资料，则“完善资料，更新个人主页”， 点击跳转到个人主页， status=1，
     *       假如已更新过资料，则“请等待审核完成”，不能点击，status=2，
     *    假如6条件没有都满足
     *       假如我连手机号都填过了，   则“请等待审核完成”，不能点击，status=3，
     *       假如手机号未填过
     *         假如钱付过了，则“连线导师进入快速认证通道”，点击跳转到手机号填写页面，status = 4，
     *         假如钱没付过，则“连线导师进入快速认证通道”，点击跳转到显示价格50元页面。status= 5，
     * 
     **/
    public function index($uid)
    {
        //xieye ,201803 现在我要查找 vip童星群。
        $db = Sys::get_container_db_eloquent();
        $sql = "select summary,title,pic,qrcode_pic,type,code,group_or_person from bb_group
                 where bb_type= 3 order by id desc limit 1";
        $group = DbSelect::fetchRow($db, $sql);
        $wx_group = null;
        if ($group) {
                    $wx_group = $group;
                    unset($wx_group['type']);
                    $wx_group['pic'] = PicPrefixUrl::add_pic_prefix_https($wx_group['pic'], 1);
                    $wx_group['qrcode_pic'] = PicPrefixUrl::add_pic_prefix_https($wx_group['qrcode_pic'], 1);
            
        }
        //return ['qq_group'=> $qq_group, 'wx_group'=>$wx_group];
        
        
        $uid = intval($uid);
        $help = new \BBExtend\user\Vip($uid);
        $complete= $help->statistic();
        
       
        
        
        $status = $help->status( $complete );
        $status_word='';
        
        if ($status==1) {
            $status_word='完善资料，更新个人主页';
        }
        if ($status==2) {
            $status_word='请等待审核完成';
        }
        if ($status==3) {
            $status_word='请等待审核完成';
        }
        if ($status==4) {
            $status_word='连线导师进入快速认证通道';
        }
        if ($status==5) {
            $status_word='连线导师进入快速认证通道';
        }
        if ($status==7) {
            $status_word='完善资料，更新个人主页';
        }
        
        
        $daoshi_allow = $this->is_daoshi_allow($uid);
        $jigou_allow = $this->is_jigou_allow($uid);
        if ( $daoshi_allow && $jigou_allow ) {
            
        }else {
            $status = 8;
            $status_word='您有其他申请，不能操作';
            
        }
        
        return [
                'code'=>1,
                'data'=>[
                        'complete' =>$complete,
                        'group' => $wx_group,
                        'status' => $status,
                        'status_word' => $status_word,
                         'money' =>50,                
                        'list'=>[
                                [
                                        'number' => $help->dengji,
                                        'word' => '等级达到10级',
                                ],
                                [
                                        'number' => $help->chengjiu,
                                        'word' => '成就达到3个',
                                ],
                                [
                                        'number' => $help->record,
                                        'word' => '通过审核视频20个',
                                ],
                                [
                                        'number' => $help->guanzhu,
                                        'word' => '关注用户50人',
                                ],
                                [
                                        'number' => $help->fensi,
                                        'word' => '粉丝数50人',
                                ],
                                [
                                        'number' => $help->huodong,
                                        'word' => '参与活动3次',
                                ],
                                
                        ],
                ],
        ];
        
        
    }
    
    
    // 即是机构自身允许，又是 机构其他允许
    private function is_jigou_allow($uid)
    {
        $db = Sys::get_container_dbreadonly();
        $sql = "select * from bb_brandshop_application where uid=? limit 1";
        $row = $db->fetchRow($sql, [ $uid ]);
        if (!$row) {
            return true;
        }
        if ( $row['status']==2 ) {
            return true;
        }
        if ( $row['status']==0 ) {
            $this->message='您已申请品牌馆，请等待审核';
        }else {
            $this->message='您已申请成功，无需审核';
        }
        return false;
        
    }
    
    
    // 即是打赏自身允许，又是 打赏其他允许
    private function is_daoshi_allow($uid)
    {
        $db = Sys::get_container_dbreadonly();
        $sql = "select * from bb_starmaker_application where uid=? limit 1";
        $row = $db->fetchRow($sql, [ $uid ]);
        if (!$row) {
            return true;
        }
        if ( $row['status']==2 ) {
            return true;
        }
        
        if ( $row['status']==0 ) {
            $this->message='您已申请导师，请等待审核';
        }else {
            $this->message='您已申请成功，无需审核';
        }
        return false;
    }
    
}

