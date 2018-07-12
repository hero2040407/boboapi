<?php
namespace app\api\controller;
use think\Db;


class Config
{

    public function jubao()
    {
        $data   = Db::table('bb_config_jubao')->select();
        
        foreach ($data as $k=>$v) {
            $data[$k]['name'] = $v['content'];
            unset($data[$k]['content']);
        }
        
        return ['code'=>1, 'data'=>$data];
    }
    
    public function aliyun_dir()
    {
        
        
        $data= [
                'header_pic_upload_dir'=> 'uploads/headpic_date/'.date("Ymd"),
                'apply_pic_upload_dir'=> 'uploads/apply_date/'.date("Ymd"),
                
        ];
        return ['code'=>1,'data'=>$data ];
    }
    
}
