<?php
namespace app\backstage\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

use think\Controller;
use think\Request;

class Test  
{
    
   
   
    
    /**
     * 未过期大赛
     * 
     * @return number[]|string[]|number[]
     */
    public function index($per_page=10,$aaa=1) 
    {
        $request = Request::instance( );
        $controller = $request->controller();
        
        $action = $request->action( );
        $route = $controller . '/' . $action;
        echo 'domain: ' . $request->domain() . '<br/>';
        echo $route;
    }
    
  
    
        
}




