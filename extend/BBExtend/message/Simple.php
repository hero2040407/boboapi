<?php
namespace BBExtend\message;

/**
 * 
 
 * 
 * 
 * 
 * User: 谢烨
 */


class Simple
{
    public $content;
    public $url;
    public $color;
    
    public static function getinstance()
    {
        return new self();
    }
    
    public function content($c)
    {
        $this->content=strval( $c);
        return $this;
    }
    
    public function get_color()
    {
        return $this->color;
    }
    
    public function get_url()
    {
        return $this->url;
    }
    public function get_content()
    {
        return $this->content;
    }
    
    public function color($color)
    {
        $this->color=$color;
        return $this;
    }
    
    public function url($url)
    {
        $this->url =$url;
        return $this;
    }
    
    public function get()
    {
        $temp=[];
        if ($this->content) {
            $temp['content'] = $this->content;
        }
        if ($this->url) {
            $temp['url'] = $this->url;
        }
        if ($this->color ) {
            $temp['color'] = $this->color ;
        }
        if (isset( $temp['content'] ) ) {
          return $temp;
        }else {
            return [];
        }
    }
    
}

