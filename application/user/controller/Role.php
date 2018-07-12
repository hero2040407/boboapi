<?php
/**
 * 用户个人信息
 */

namespace app\user\controller;

use BBExtend\Sys;

class Role
{
    
    private $message='';
    
    
    /**
     * 总结一下，当用户申请某一个角色时，需自身允许，两个其他允许。
                        而自身允许，和其他允许，不同。
                        
                        导师自身允许-1 2
                        机构自身允许 -1 2
                        童星自身允许，一定允许。
                        
                        导师其他允许，-1 2
                        机构其他允许 -1 2
                        童星其他允许：查最新一条，是5的情况，允许。

     * @param unknown $uid
     * @param number $role
     * @return number[]|string[]|number[]|NULL[][]|number[][]|unknown[][]|mixed[][]
     */
    public function get_role_status($uid,$role=0)
    {
        $uid = intval($uid);
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code'=>0,'message' =>'用户不存在' ];
        }
        
        $result = false;
        $message='';
        
        if ($role==2) {
            // 申请导师
            $daoshi_allow = $this->is_daoshi_allow($uid);
            $jigou_allow = $this->is_jigou_allow($uid);
            $vip_allow = $this->is_vip_allow($uid);
            
            
            if ($daoshi_allow && $jigou_allow && $vip_allow ) {
                $result = true;
            }else {
                if (!$daoshi_allow) {
                    $message = $this->message;
                }else {
                   $message = "您已申请其他角色，不可重复申请";
                }
            }
        }
        
        if ($role==4) {
            // 申请机构
            $daoshi_allow = $this->is_daoshi_allow($uid);
            $jigou_allow = $this->is_jigou_allow($uid);
            $vip_allow = $this->is_vip_allow($uid);
            
            
            if ($daoshi_allow && $jigou_allow && $vip_allow ) {
                $result = true;
            }else {
                if (!$jigou_allow) {
                    $message = $this->message;
                }else {
                   $message = "您已申请其他角色，不可重复申请";
                }
            }
        }
        
        return [
                'code'=>1,
                'data'=> [
                        'role'=>$user->role,
                        'message' => $message,
                        'result' => $result,
                        
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
    
    // 童星其他允许 
    private function is_vip_allow($uid)
    {
        $db = Sys::get_container_dbreadonly();
        $sql = "select * from bb_vip_application_log where uid=? order by id desc limit 1";
        $row = $db->fetchRow($sql, [ $uid ]);
        if (!$row) {
            return true;
        }
        if ( $row['status']==5 ) {
            return true;
        }
        $this->message='您已申请其他角色，不可重复申请';
        return false;
    }
    
    
    
    
    
   
    
}