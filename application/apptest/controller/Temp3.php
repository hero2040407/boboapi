<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Controller;
use think\Request;


class Temp3 extends  Controller
{
    public function _initialize(){
//         session("backstage_islogin",1);
//         session("backstage_id", 22 );
        
        $temp = Request::instance()->param();
        if ($temp) {
            foreach ($temp as $k=>$v) {
               echo "111--{$k}--{$v}<br>";
               Request::instance()->param([$k => $v ]);
            }
        }
        
       // dump($temp);
        
         Request::instance()->param(['register_id' => 99 ]);
       // Request::instance()->post(['i' => 112 ]);
        
//         Request::instance()->get(['id2' => 339 ]);
//         Request::instance()->post(['id2' => 3390 ]);
//         Request::instance()->param(['id2' => 3390 ]);
//         Request::instance()->param(['field_id' => 33901 ]);
        
        
        //    echo "_initialize<br>";
    }
    
}





