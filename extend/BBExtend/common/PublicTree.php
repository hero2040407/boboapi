<?php

namespace BBExtend\common;
use think\Db;
/**
 * 通用树组件
 * 
 * 
 * @author 谢烨
 */
class PublicTree
{
    private $select_name;
    private $server_url;
    private $server_url_full;
    private $yuming;
    
    public function __construct($name="area_tree")
    {
        global  $g_var;
        $this->yuming =  $g_var['site_url'];
        
        $this->select_name = $name;
        $this->server_url=  '/area/tree'; //3级
        $this->server_url_full =  '/area/treefull';  //4级
    }
    
    
    
    /**
     * 返回北京->北京
     *     上海->上海
     *     江苏->南京
     *     江苏->苏州
     *     江苏->徐州
     *     浙江->杭州
     * @param number $id
     * @return string
     */
    public function htmlcity($id=0)
    {
        $this->server_url =  '/area/treecity';
     // debug(55);
        return $this->html($id);
    }
    
    /**
     * 城市固定，但是还有4级。
     */
    public function html_selected_city($city_id,$id)
    {
        $city_id = intval($city_id);
        $this->server_url_full =  "/area/treeselectedcity/cityid/{$city_id}";
        // debug(55);
        return $this->htmlfull($id);
    }
    
    
    /**
     * 返回标准的3级树
     * @param number $id
     */
    public function html($id=0)
    {
        $server_url = $this->server_url; 
        $name = $this->select_name;
        $val = '';
        if ($id) {
            $val = $this->get_link_str_quote($id);
           
        }
        
        $html = "
<input type='text' name='{$name}' style='display:none' />
<input type=hidden name='{$name}_v' value='0' />
<script type='text/javascript'>
$(function() {
    var options = {
            empty_value: 'null',
            indexed: true,  
            on_each_change: '{$server_url}', 
            choose: function(level) {
                return '请选择' ;
            }
            ,preselect: {'{$name}': [{$val}]}
    };
    $.getJSON('{$server_url}', function(tree) { 
        $('input[name={$name}]').optionTree(tree, options).change();
    });
});
function getreal(temp){
    if (!temp) {
        return 0;
    }else{
        return temp;
    }
}
function check_tree(name){
    
    if ($('[name='+ name +'___]').length > 0) {
       var temp3=  getreal($('[name='+ name +'___]').val());
       if (!temp3) {
          return false;
       }else { return true; }
    }
    if ($('[name='+ name +'__]').length > 0) {
       var temp2=  getreal($('[name='+ name +'__]').val());
        if (!temp2) {
          return false;
       }else { return true; }
    }
    var temp1 = 0;
    if ($('[name='+ name +'_]').length > 0) {
       var temp1=  getreal($('[name='+ name +'_]').val());
        if (!temp1) {
          return false;
       }else { return true; }
    }
}
function get_tree_value(name){
   var temp3 = 0;
    if ($('[name='+ name +'___]').length > 0) {
       var temp3=  getreal($('[name='+ name +'___]').val());
    }
    var temp2 = 0;
    if ($('[name='+ name +'__]').length > 0) {
       var temp2=  getreal($('[name='+ name +'__]').val());
    }
    var temp1 = 0;
    if ($('[name='+ name +'_]').length > 0) {
       var temp1=  getreal($('[name='+ name +'_]').val());
    }
    if (temp3>0) {
        return temp3;
    }
    if (temp2>0){
        return temp2;
    }
    return temp1;
}

</script>                
                ";
        return $html;
    }
    
    
    
    
    /**
     * 返回标准的4级树
     * @param number $id
     */
    public function htmlfull($id=0)
    {
        $server_url = $this->server_url_full;
        $name = $this->select_name;
        $val = '';
        if ($id) {
            $val = $this->get_link_str_quote($id);
            //debug($val);
        }
    
        $html = "
<input type='text' name='{$name}' style='display:none' />
<input type=hidden name='{$name}_v' value='0' />
<script type='text/javascript'>
$(function() {
    var options = {
        empty_value: 'null',
        indexed: true,
        on_each_change: '{$server_url}',
        choose: function(level) {
            return '请选择' ;
        }
        ,preselect: {'{$name}': [{$val}]}
    };
    $.getJSON('{$server_url}', function(tree) {
        $('input[name={$name}]').optionTree(tree, options).change();
    });
});

function getreal(temp){
    if (!temp) {
        return 0;
    }else{
        return temp;
    }
}
function check_tree(name){
    if ($('[name='+ name +'____]').length > 0) {
        var temp4=  getreal($('[name='+ name +'____]').val());
        if (!temp4) {
           return false;
        }else { return true; }
    }    

    if ($('[name='+ name +'___]').length > 0) {
        var temp3=  getreal($('[name='+ name +'___]').val());
        if (!temp3) {
           return false;
        }else { return true; }
    }
    if ($('[name='+ name +'__]').length > 0) {
        var temp2=  getreal($('[name='+ name +'__]').val());
        if (!temp2) {
            return false;
        }else { return true; }
    }
    var temp1 = 0;
    if ($('[name='+ name +'_]').length > 0) {
        var temp1=  getreal($('[name='+ name +'_]').val());
        if (!temp1) {
            return false;
        }else { return true; }
    }
}
function get_tree_value(name){
    var temp4 = 0;
    if ($('[name='+ name +'____]').length > 0) {
        var temp4=  getreal($('[name='+ name +'____]').val());
    }
    var temp3 = 0;
    if ($('[name='+ name +'___]').length > 0) {
        var temp3=  getreal($('[name='+ name +'___]').val());
    }
    var temp2 = 0;
    if ($('[name='+ name +'__]').length > 0) {
        var temp2=  getreal($('[name='+ name +'__]').val());
    }
    var temp1 = 0;
    if ($('[name='+ name +'_]').length > 0) {
        var temp1=  getreal($('[name='+ name +'_]').val());
    }
    if (temp4>0) {
        return temp4;
    }
    if (temp3>0) {
        return temp3;
    }
    if (temp2>0){
        return temp2;
    }
    return temp1;
}
    

function get_tree_list(name) {
      
}

</script>
    ";
        return $html;
    }
    
    
    
    
    
    
    
    
    
    
    
    public function get_link_str_quote($id) {
        $result = $this->get_link_str($id);
        $arr = explode(',', $result);
        $arr2 = array();
        foreach ($arr as $v  ) {
            $arr2[] = "'{$v}'";
        }
        return implode(',', $arr2);
    }
    
    public function get_link_str($id)
    {
       
        if (!$id) {
            return '0';
        }
        $id = intval($id);
        $sql ="select * from bb_area where id= {$id}";
        $result = Db::query($sql);
        $result = $result[0];
        return $result['path'];
// //         $result = $db->fetchRow($sql);
//         if ($result['level']==1) {
//             return  strval($id);
//         }else {
//             return $result['path'].','.$id;
//         }
    }
    
    /**
     * 添加商户时，判断地区链是否包含开通的城市id
     * @param unknown $id
     */
    public function include_ison($id)
    {
        $area_str = $this->get_link_str($id);
        $arr = explode(',', $area_str);
        $db = Sys::get_container_db();
        $sql = "select areaID from area where ison=1";
        $ison_arr = $db->fetchCol($sql);
        foreach ($arr as $area) {
            if (in_array($area, $ison_arr)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 添加商户时，判断地区链是否包含 管理员地区id
     * @param unknown $id
     */
    public function include_admin_area($id, $admin_area)
    {
        $area_str = $this->get_link_str($id);
        $arr = explode(',', $area_str);
//         $db = Sys::get_container_db();
//         $sql = "select areaID from area where ison=1";
//         $ison_arr = $db->fetchCol($sql);
        foreach ($arr as $area) {
            if ( $admin_area == $area ) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 添加商户时,根据地区id得到城市id
     * @param unknown $id
     */
    public function get_city_id_by_area_id($area_id)
    {
        $area_str = $this->get_link_str($area_id);
        $arr = explode(',', $area_str);
        
        $db = Sys::get_container_db();
        $sql = "select areaID from area where is_target_city=1";
        $ison_arr = $db->fetchCol($sql);
        foreach ($arr as $area) {
            if (in_array($area, $ison_arr)) {
                return $area;
            }
        }
        return 0;
    }
    
    /**
     * 帮助，根据一个areaid得到江苏省南京市六合区，这样的字符串
     * @param unknown $id
     */
    public function get_area_name_stream_by_id($area_id)
    {
        $area_str = $this->get_link_str($area_id);
        $arr = explode(',', $area_str);
        $s ='';
        $db = Sys::get_container_db();
//         $sql = "select areaName from area where ison=1";
//         $ison_arr = $db->fetchCol($sql);
        foreach ($arr as $area) {
            $sql = "select areaName from area where areaID = ".intval($area);
            $s .= $db->fetchOne($sql);
        }
        return $s;
    }
    
  
}//end class

