<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17 0017
 * Time: 上午 9:31
 */
namespace app\backstage\controller;

use BBExtend\backmodel\CommonSelection;

class Commonselectconfig extends Common
{
    public function create()
    {
        
    }

    public function index()
    {
        $list = CommonSelection::all();
        $this->success('','',$list);
    }
    
    public function read()
    {
        
    }

    public function delete()
    {
        
    }
}