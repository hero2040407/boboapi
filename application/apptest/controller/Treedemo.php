<?php

namespace app\apptest\controller;

use think\Db;
use BBExtend\common\PublicTree;

// use app\shop\model\Area;
/**
 * 谢烨20160914，把杨桦的地区数据，导入到我方的数据库里bb_area
 * 
 * @author Administrator
 *        
 */
class Treedemo {
    // $temp='';
    public function index() {
        echo "<h2>标准3列选定 六合区id=1186</h2>";
        $js = "
<script type='text/javascript' src='/public/js/jquery-1.9.1.min.js'></script>
<script type='text/javascript' src='/public/js/jquery-option-tree/jquery.optionTree.js'></script>         
  <button onclick='var a=get_tree_value(\"tree\");alert(a)'>查地区值</button>
  <button onclick='var a=check_tree(\"tree\");alert(a)'>检查是否选择</button>      
         ";
        echo $js;
        $tree = new PublicTree ( 'tree' );
        echo $tree->html ( 1186 );
        
        echo "<hr><h2>标准3列不选定</h2>";
        echo "<br><br>
         <button onclick='var a=get_tree_value(\"tree2\");alert(a)'>查地区值2</button>
          <button onclick='var a=check_tree(\"tree2\");alert(a)'>检查是否选择</button>
         ";
        $tree23 = new PublicTree ( 'tree2' );
        echo $tree23->html ();
    }
}
