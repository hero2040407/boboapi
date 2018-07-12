<?php
namespace app\area\controller;
use think\Db;
class Tree
{
    public function index()
    {
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
        }else {
            $id=0;
        }
        
       // $this->noTpl();
       // Zend_Json::$useBuiltinEncoderDecoder = true;
     //   echo Zend_Json::encode($this->getDirectDescendants( intval($_GET['id']) ));
       // echo json_encode(  $this->getDirectDescendants( $id )  );
        return $this->getDirectDescendants( $id );
    }

    public function aa()
    {
        echo 333;
    }
    
    /**
     * 3级树
     * @param unknown $line_no
     */
    private function getDirectDescendants($line_no = null) {
       
        if (!$line_no) {
            $sql ="select id,name from bb_area where level=1";
            $result = Db::query($sql);
        }else {
            $sql ="select id,name from bb_area where parent={$line_no} and level <4";
            $result = Db::query($sql);
        }
        $new = array();
        foreach ($result as $v)
        {
            $s = $v['id'];
            $v2 = $v['name'];
            $new[$v['id']] = $v2;
        }
        return $new;
    }
    
}