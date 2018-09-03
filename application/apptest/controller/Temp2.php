<?php
namespace app\apptest\controller;


class Temp2 extends Temp4 
{
    
   //程序入口。  
   public function index()
   {
       $this->index22();
       
   }
   
   public function index3(){
       echo "index3;";
   }
   public function index5(){
       echo "index5;";
   }
   
  
   
}

class Temp4{
    
    public function index22(){
        $this->index3();
        $this->index4();
        $this->index5();
    }
    
    public function index4(){
        echo "index4;";
    }
}



