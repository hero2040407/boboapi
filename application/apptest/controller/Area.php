<?php
//  1    2    3
namespace app\apptest\controller;
    

use  think\Db;
//use app\shop\model\Area;
/**
 * 谢烨20160914，把杨桦的地区数据，导入到我方的数据库里bb_area
 * @author Administrator
 *
 */
class Area
{
  //$temp='';
    public function index(){
        return;
        $filename = APP_PATH . 'apptest/controller/1.csv';
        $s2 = file($filename);
        $s = array();
        
        foreach ($s2 as $line) {
            $arr = explode(',', $line);
            $s[] = $arr;
        }
       
        $i=0;
        foreach ($s as $k=> $arr) {
            if ($arr[4] ==1) {
                $s[$k]['use'] =1;
                $s[$k]['is_city'] =0;
                
                
                $i++;
//                 echo $i."<br\n>";
//                 var_dump($arr);
                
                $obj = new \app\shop\model\Area();
                $obj->data('postcode', $arr[0]);
                $obj->data('name', $arr[3]);
                $obj->data('level', 1);
                $obj->data('parent', 0); //省没有父节点。
                $obj->data('amap_code', trim(strval($arr[5]))); 
                $obj->data('shortpy', trim(strval($arr[6])) );
                $obj->data('fullpy', trim(strval($arr[7])) );
                $obj->save();
                $s[$k]['id'] = $obj->getData("id");
            }else {
                $s[$k]['use'] =0;
                $s[$k]['id'] =0;
                $s[$k]['is_city'] =0;
            }
        }
     // file_put_contents("1.txt", var_export( $s ,1)); return;
      
        $s2 = $s;
      
        foreach ($s as $k=> $arr) {
           
            if ($s[$k]['use']==1 ) {
                //从这里开始，把每个省遍历哦。
                $pid = $s[$k]['id'];
                $parent_code = $arr[0];
                
                foreach ($s2 as  $k2=>$arr2) {
                    if ($arr2[4] == $parent_code ) {
                        echo  $parent_code." : ".$this->u2g( $arr2[2])."\n";
                        
                        $obj = new \app\shop\model\Area();
                        $obj->data('postcode', $arr2[0]);
                        $obj->data('name', $arr2[2]);
                        $obj->data('level', 2);
                        $obj->data('parent', $pid); //省没有父节点。
                        $obj->data('amap_code', trim(strval($arr2[5])));
                        $obj->data('shortpy', trim(strval($arr2[6])) );
                        $obj->data('fullpy', trim(strval($arr2[7])) );
                        $obj->save();
                        $s2[$k2]['is_city'] = $obj->getData('id');
                        
                    }
                }
                //continue;
            }
//             echo $line[0];
        }
       //file_put_contents("1.txt", var_export( $s2 ,1)); return;
        $s=$s2;
        foreach ($s as $k=> $arr) {
            if ($s[$k]['is_city'] ) {
                //从这里开始，把每个市遍历。
                $pid = $s[$k]['is_city'];
                $parent_code = $arr[0];
                $i2=0;
                foreach ($s2 as  $k2=>$arr2) {
                    if (in_array($arr2[1], array('其他区','其它区'))) {continue;}
                    if ($arr2[4] == $parent_code ) {
                        echo  $parent_code." : ".$this->u2g( $arr2[2])."\n";
                        $i2++;
                        $obj = new \app\shop\model\Area();
                        $obj->data('postcode', $arr2[0]);
                        $obj->data('name', $arr2[1]);
                        $obj->data('level', 3);
                        $obj->data('parent', $pid); //省没有父节点。
                        $obj->data('amap_code', trim(strval($arr2[5])));
                        $obj->data('shortpy', trim(strval($arr2[6])) );
                        $obj->data('fullpy', trim(strval($arr2[7])) );
                        $obj->save();
                    }
                }
                if ($i2==0) {
                    $obj = new \app\shop\model\Area();
                    $obj->data('postcode', $arr[0]);
                    $obj->data('name', $arr[1]);
                    $obj->data('level', 3);
                    $obj->data('parent', $pid); //省没有父节点。
                    $obj->data('amap_code', trim(strval($arr[5])));
                    $obj->data('shortpy', trim(strval($arr[6])) );
                    $obj->data('fullpy', trim(strval($arr[7])) );
                    $obj->save();
                }
            }
        }
        echo "insert  ok \n\n";
        
        $this->set_path();
        
        echo "set_path1  ok \n\n";
        
        $this->set_wordpath();
        //首先，插入省。
        echo "set_path2  ok \n\n";
        
//         echo $a1;
//         echo $a2;
    }
    
    
    
    
    public function set_wordpath()
    {
        $sql ="select id from bb_area order by id";
        $result = Db::query($sql);
        $arr = array();
        //  var_dump($result);
        foreach ($result as $v) {
            $arr[] = $v["id"];
        }
        
        foreach ($arr as $id) {
            $sql ="select path from bb_area where id={$id}";
            $result = Db::query($sql);
            $path = $result[0]["path"];
            $newarr=[];
            $arr = explode(",", $path); 
            foreach ($arr as $v) {
                $sql ="select name from bb_area where id ={$v}" ;
                $temp = Db::query($sql);
                $newarr[]= $temp[0]['name'];
            }
            $str = implode(',', $newarr);
            $sql ="update bb_area set wordpath='{$str}' where id={$id}";
            Db::query($sql);
        }
        
//         $idd = $this->get_parent(6843);;
//         echo $idd;
    }
    
    public function set_path()
    {
        $sql ="select id from bb_area order by id";
        $result = Db::query($sql);
        $arr = array();
      //  var_dump($result);
        foreach ($result as $v) {
            $arr[] = $v["id"];
        }
        foreach ($arr as $id) {
            echo $id."\n";
            $path = $this->get_parent($id);
            
            $sql = "update bb_area set path='{$path}' where id={$id}";
            Db::query($sql);
        }
        
    }
    
    public function get_parent($id) {
        $sql = "select parent from bb_area where id=".intval($id);
        $result = Db::query($sql);
        $parent = $result[0]['parent'];
        if (!$parent) {
            return $id; // 说明是省级节点。
        }else {
            $sql = "select parent from bb_area where id=".intval($parent);
            $result = Db::query($sql);
            $parent2 = $result[0]['parent'];
            if (!$parent2) {
                return "{$parent},{$id}";
            }else {
//                 $sql = "select parent from bb_area where id=".intval($parent2);
//                 $result = Db::query($sql);
//                 $parent3 = $result[0]['parent'];
                return "{$parent2},{$parent},{$id}";
            }
        }
    }
    
    public function u2g($s)
    {
        return mb_convert_encoding($s, 'GBK', 'UTF-8');
    }
   
}
