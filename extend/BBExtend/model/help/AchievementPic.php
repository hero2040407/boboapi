<?php

namespace BBExtend\model\help;

use Illuminate\Database\Eloquent\Model;
use BBExtend\model\ConfigStr;

/**
 * 成就
 */
class AchievementPic 
{
    
    public $config_arr = null;
    public $server_url;
    public function __construct() {
        $this->server_url = \BBExtend\common\BBConfig::get_server_url ();
    }
    
    /**
     * 根据event的key和级别val得到图片。
     *
     * @param unknown $key            
     * @param unknown $val            
     */
    public function get_pic_by_key($key, $val, $full=true) {
        $arr = $this->get_config_arr ();
        
        $key = $key . $val;
        if (isset ( $arr [$key] )) {
            
            $pic = $arr [$key];
            if ((! preg_match ( '/^http/', $pic ))  && $full ) {
                $pic = $this->server_url . $pic;
            }
            
            return $pic;
        } else {
            return $this->server_url . '/public/toppic/topdefault.png';
        }
    }
    
    /**
     * 获得全体配置，type=1，即成就图片。
     */
    private function get_config_arr() {
        if ($this->config_arr) {
            return $this->config_arr;
        } else {
            $config = ConfigStr::where ( "type", 1 )->get ();
            $config_arr = json_decode ( $config->toJson (), 1 );
            $arr = ( array ) $config_arr;
            $new = [ ];
            foreach ( $arr as $v ) {
                $new [$v ['config']] = $v ['val'];
            }
            
            $this->config_arr = $new;
            return $this->config_arr;
        }
    }
    
    /**
     * 根据事件得到中文名
     * @param unknown $event
     * @return string
     */
    public static function get_event_name($event) {
        switch ($event) {
            case "dengji" :
                return "等级达人";
            case "zhibo" :
                return "直播达人";
            case "pinglun" :
                return "评论达人";
            case "dianzan" :
                return "点赞达人";
            case "zhubo" :
                return "优质主播";
            case "hongren" :
                return "BOBO小红人";
            case "huodong" :
                return "活动达人";
            case "dasai" :
                return "大赛达人";
            case "neirong" :
                return "内容缔造者";
            default :
                return "";
        }
    }
}
