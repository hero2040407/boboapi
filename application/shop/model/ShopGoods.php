<?php
namespace app\shop\model;

use think\Model;

/**
 * 商品模型类
 * @author Administrator
 *
 */
class ShopGoods extends Model
{
    protected $table = 'bb_shop_goods';
    
    /**
     * 正确的波币价格，必须整数
     */
    public function right_currency()
    {
        $currency = $this->getData('currency');
        if ($currency && $currency == -1 ) {
            return $currency;
        }
        //特别判断，价格不能为0，谢烨20160825
        if ($currency <1) {
            $currency =1;
        }
        
        $discount = $this->getData('discount');
        if ($discount == 10) { //原始折扣为10，则直接返回原始价格。
            return $currency;
        }
        
        //现在就要判断促销时间了。在促销期间内又有原始折扣，则应该打折。
        $time = time();
        if ($time >= $this->getData('on_sale_start_time')
            &&
            $time <= $this->getData('on_sale_end_time')  ) {
            $temp = intval($currency * ( $discount /10));
            if ($temp <1) {
                $temp=1;
            }
            return $temp;
        }
        return $currency; //有折扣但时间不对，则返回原始价格
    }
    
    /**
     * 正确的积分，必须整数
     */
    public function right_score()
    {
        $currency = $this->getData('exchange_score');
        if ($currency && $currency == -1 ) {
            return $currency;//表示禁止积分
        }
        //特别判断，积分不能为0，谢烨20160825
        if ($currency <1) {
            $currency =1;
        }
    
//         $discount = $this->getData('discount');
//         if ($discount == 10) { //原始折扣为10，则直接返回原始价格。
//             return $currency;
//         }
    
//         //现在就要判断促销时间了。在促销期间内又有原始折扣，则应该打折。
//         $time = time();
//         if ($time >= $this->getData('on_sale_start_time')
//                 &&
//                 $time <= $this->getData('on_sale_end_time')  ) {
//                     $temp = intval($currency * ( $discount /10));
//                     if ($temp <1) {
//                         $temp=1;
//                     }
//                     return $temp;
//                 }
                return $currency; //有折扣但时间不对，则返回原始价格
    }
    
    
    
    private function format($money)
    {
        return sprintf("%.2f", $money);
    }
    
    public function format_money()
    {
       $money =  $this->getData('money');
       if ($money > 0) {
         return $this->format( $this->getData('money'));
       }else {
           return $money;
       }
    }
    
    /**
     * 正确的现金价格
     */
    public function right_money()
    {
         $money =   $this->getData('money');
        if ($money < 0 ) {
            return $money;
        }
        $money =  $this->format( $this->getData('money'));
        $discount = $this->getData('discount');
        if ($discount == 10) { //原始折扣为10，则直接返回原始价格。
            return  $money;
        }
        //现在就要判断促销时间了。在促销期间内又有原始折扣，则应该打折。
        $time = time();
        if ($time >= $this->getData('on_sale_start_time')
            &&
            $time <= $this->getData('on_sale_end_time')  ) {
            return $this->format( $money * ( $discount /10));
        }
        return  $money; //有折扣但时间不对，则返回原始价格
    } 

    
    /**
     * 这是 与样式有关的图片属性。
     *
     * 谢锋设置了 商品图片 的默认地址。
     *
     * @return string
     */
    public function  right_style_pic($style='')
    {
         
        if ($style) {
            $arr = $this->right_pic_list_arr();
            $pic='';
            foreach ($arr as $v) {
                if ($v['style'] == $style) {
                    $pic = $v['picpath'];
                }
            }
            if (!$pic) {
                return $this->right_pic();
            }
            return $pic;
        } else {
            return $this->right_pic();
        }
        
    }
    
    
    /**
     * 确保图片有域名
     * 
     * 谢锋设置了 商品图片 的默认地址。
     * 
     * @return string
     */
    public function  right_pic()
    {
       
        $temp = $this->right_show_pic_list_arr();
        if ($temp) {
            //如果有，应该取出第一张。
            return $temp[0]['picpath'];
        }
        //如果 录播图存在，则一定为kong
        $temp = $this->right_pic_list_arr();
        if ($temp) {
            //如果有，应该取出第一张。
            return $temp[0]['picpath'];
        }
        
        
        $pic = $this->getData('pic');
        if (!$pic)  {
            return  \BBExtend\common\BBConfig::get_server_url() . '/public/shop_goods/default.png';
        }
        
        return  \BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(  $pic );
    }
    
    /**
     * 返回图片列表的json字符串，加域名
     */
    public function right_pic_list_json()
    {
        $arr = $this->right_pic_list_arr();
        return json_encode($arr, true);
    }
    
    /**
     * 返回图片列表的数组，加域名
     */
    public function right_pic_list_arr()
    {
        $json = $this->getData('pic_list');
        if (!$json) {
            return '';
        }
        $pic_list = json_decode($json, true);
        $server_url = \BBExtend\common\BBConfig::get_server_url();
        $pic_list=(array)$pic_list;
        foreach ($pic_list as $k => $v ) {
            if ( strpos($v['picpath'], 'http') === false ) {
                $pic_list[$k]['picpath'] = $server_url . $v['picpath'];
            }
        }
        return $pic_list;
    }
    
    /**
     * 返回show图片列表
     */
    public function right_show_pic_list_arr()
    {
        $json = $this->getData('show_pic_list');
        if (!$json) {
            return '';
        }
        $pic_list = json_decode($json, true);
        $server_url = \BBExtend\common\BBConfig::get_server_url();
        foreach ($pic_list as $k => $v ) {
            if ( strpos($v['picpath'], 'http') === false ) {
                $pic_list[$k]['picpath'] = $server_url . $v['picpath'];
            }
        }
        return $pic_list;
    }
    
    
}