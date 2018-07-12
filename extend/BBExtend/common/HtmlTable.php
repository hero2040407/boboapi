<?php
namespace  BBExtend\common;

/**
 * 
 *
 * @author 谢烨
 */
class HtmlTable  
{
    private $title_arr;
    private $content_arr;
    
    /**
     * 
     */
    public function __construct($title_arr, $content_arr)
    {
        $this->title_arr = $title_arr;
        $this->content_arr = $content_arr;
    }
    
    
    /**
     * 得到html代码
     * 
     */
    public function to_html()
    {
        $html="
<table class='bordered'>
  
    <tr>
";
        foreach ($this->title_arr as $v) {
            $html .="
      <th>{$v}</th>                        
";
        }
        $html .= "
    </tr>
  
";
        $i=0;
        foreach ($this->content_arr as $arr) {
            $i++;
            $html .="  <tr>\n";
            foreach ($arr as $v) {
                $bgcolor='';
                if ($i%2==0) {
                    $bgcolor = " bgcolor='#e5f1d6' ";
                }
                $html .= "    <td {$bgcolor} >{$v}</td>\n";
            }
            $html .="  </tr>\n";
        }
        $html .="</table>";
        return $this->add_css(). $html;
    }
    
    public function add_css(){
        $css3 ="
<style>

.bordered {
    border-spacing: 0;
    width: 100%;   
    border: solid #ccc 1px;
    -moz-border-radius: 6px;
    -webkit-border-radius: 6px;
    border-radius: 6px;
    -webkit-box-shadow: 0 1px 1px #ccc; 
    -moz-box-shadow: 0 1px 1px #ccc; 
    box-shadow: 0 1px 1px #ccc;         
}

.bordered tr:hover {
    background: #fc6;
    -o-transition: all 0.1s ease-in-out;
    -webkit-transition: all 0.1s ease-in-out;
    -moz-transition: all 0.1s ease-in-out;
    -ms-transition: all 0.1s ease-in-out;
    transition: all 0.1s ease-in-out;     
}    
    
.bordered tr:hover td {
    background: #fc6;
}                 
                
.bordered td, .bordered th {
    border-left: 1px solid #ccc;
    border-top: 1px solid #ccc;
    padding: 4px;
    text-align: left;    
}

.bordered th {
    background-color: #dce9f9;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#ebf3fc), to(#dce9f9));
    background-image: -webkit-linear-gradient(top, #ebf3fc, #dce9f9);
    background-image:    -moz-linear-gradient(top, #ebf3fc, #dce9f9);
    background-image:     -ms-linear-gradient(top, #ebf3fc, #dce9f9);
    background-image:      -o-linear-gradient(top, #ebf3fc, #dce9f9);
    background-image:         linear-gradient(top, #ebf3fc, #dce9f9);
    -webkit-box-shadow: 0 1px 0 rgba(255,255,255,.8) inset; 
    -moz-box-shadow:0 1px 0 rgba(255,255,255,.8) inset;  
    box-shadow: 0 1px 0 rgba(255,255,255,.8) inset;        
    border-top: none;
    text-shadow: 0 1px 0 rgba(255,255,255,.5); 
}

.bordered td:first-child, .bordered th:first-child {
    border-left: none;
}

.bordered th:first-child {
    -moz-border-radius: 6px 0 0 0;
    -webkit-border-radius: 6px 0 0 0;
    border-radius: 6px 0 0 0;
}

.bordered th:last-child {
    -moz-border-radius: 0 6px 0 0;
    -webkit-border-radius: 0 6px 0 0;
    border-radius: 0 6px 0 0;
}

.bordered th:only-child{
    -moz-border-radius: 6px 6px 0 0;
    -webkit-border-radius: 6px 6px 0 0;
    border-radius: 6px 6px 0 0;
}

.bordered tr:last-child td:first-child {
    -moz-border-radius: 0 0 0 6px;
    -webkit-border-radius: 0 0 0 6px;
    border-radius: 0 0 0 6px;
}

.bordered tr:last-child td:last-child {
    -moz-border-radius: 0 0 6px 0;
    -webkit-border-radius: 0 0 6px 0;
    border-radius: 0 0 6px 0;
}

  
</style>                
                ";
        return  $css3;
    }
    
    
    
}//end class
