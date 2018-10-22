<?php
namespace app\pay\controller;

use think\Db;
use BBExtend\Currency;
use BBExtend\message\Message;
use BBExtend\fix\TableType;
use BBExtend\fix\MessageType;

/**
 * 苹果充值控制器
 * 
 * @author xieye
 */
define('BO_GOLD',1);

class Ios 
{
    public function set_order()
    {
        $uid =  input('?param.uid')?(int)input('param.uid'):0;
        $order = input('?param.order')?(string)input('param.order'):'';
        $product_id = input('?param.product_id')?(string)input('param.product_id'):'';
        
        if (\app\user\model\Exists::userhExists($uid) > 0)
        {
            $BuyDB = Db::table('bb_buy')->where(['uid'=>$uid,'order'=>$order])->find();
            if ($BuyDB)
            {
                return ['message'=>'请不要提交重复的订单号','code'=>0];
            }
            $count = 0;
            switch ($product_id)
            {
                case 'BoVCoin60':
                    $count = 60;
                    break;
                case 'PBoVCoin60':
                    $count = 60;
                    break;
                case 'BoVCoin80':
                    $count = 160;
                    break;
                case 'BoVCoin180':
                    $count = 380;
                    break;
                case 'BoVCoin300':
                    $count = 350;
                    break;
                case 'PBoVCoin300':
                    $count = 350;
                    break;
                case 'BoVCoin600':
                    $count = 1200;
                    break;
                case 'PBoVCoin600':
                    $count = 1200;
                    break;
                case 'BoVCoin880':
                    $count = 2180;
                    break;
                case 'BoVCoin1880':
                    $count = 5880;
                    break;
                case 'BoVCoin5880':
                    $count = 20000;
                    break;
                default:
                    return  ['message'=>'product_id错误','code'=>0];
            }
            Db::table('bb_buy')->insert(['uid'=>$uid,'order'=>$order,'time'=>time(),'product_id'=>$product_id,'count'=>$count]);
           return  ['code'=>1];
        }
        return  ['message'=>'非法的UID','code'=>0];
    }
    
    public function set_receipt()
    {
        $uid = input('?param.uid')?(string)input('param.uid'):0;
        $order = input('?param.order')?(string)input('param.order'):'';
        $receipt = input('?param.receipt')?(string)input('param.receipt'):'';
        
        $money = input('?param.money')?(float)input('param.money'):0;
        
        $html = $this->acurl($uid,$order,$receipt);
        $data = json_decode($html,true);
     //  \BBExtend\Sys::debugxieye($data);
       
        //如果是沙盒数据 则验证沙盒模式
        if ($data['status']=='21007') {
            //return 
            //请求验证
            $html = $this->acurl($uid,$order,$receipt, 1); // 如果是沙盒，则请求沙盒的数据。
            $data = json_decode($html,true);
            if ($data['status']==0) {
                //订单成功
                $OrderDB = Db::table('bb_buy')->where(['uid'=>$uid,'order'=>$order])->find();
                if ($OrderDB['successful'])
                {
                    return ['message'=>'当前订单已经充值成功！','code'=>0];
                }
                 Message::get_instance()
                    ->set_title('系统消息')
                    ->add_content(Message::simple()->content('恭喜您充值成功账户充入'))
                    ->add_content(Message::simple()->content("{$OrderDB['count']}BO币" )->color(0xf4a560)  )
                    ->add_content(Message::simple()->content('，请查收。'))
                    ->set_type(MessageType::chongzhi)
                    ->set_uid($uid)
                    ->send();
                return ['data'=>$data,'code'=>1];
            }
            return ['apple server error','code'=>0];
         
           //下面肯定不是沙盒 
        } elseif ($data['status']==0) { 
            //订单成功
            $OrderDB = Db::table('bb_buy')->where(['uid'=>$uid,'order'=>$order])->find();
            if ($OrderDB['successful'])
            {
                return ['message'=>'当前订单已经充值成功！','code'=>0];
            }
            
            Currency::add_currency($uid,TableType::bb_currency_log__type_bobi  ,
                    $OrderDB['count'],'充值');
            Db::table('bb_buy')->where(['uid'=>$uid,'order'=>$order])
                ->update(['error'=>$data,'receipt'=>$receipt,'successful'=>1]);
            \BBExtend\user\Tongji::getinstance($uid)->money27($money);
            
            Message::get_instance()
                ->set_title('系统消息')
                ->add_content(Message::simple()->content('恭喜您充值成功账户充入'))
                ->add_content(Message::simple()->content("{$OrderDB['count']}BO币" )->color(0xf4a560)  )
                ->add_content(Message::simple()->content('，请查收。'))
                ->set_type(MessageType::chongzhi)
                ->set_uid($uid)
                ->send();
            return ['data'=>$data,'code'=>1];
        }
        return ['data'=>$data,'code'=>0];
    }

    
    /**
     * 对苹果服务器产生远程调用
     * 
     * @param unknown $uid
     * @param unknown $order
     * @param unknown $receipt_data
     * @param number $sandbox
     */
    private function acurl($uid,$order,$receipt_data, $sandbox=0){

        //小票信息
        $POSTFIELDS = array("receipt-data" => $receipt_data);
        $POSTFIELDS = json_encode($POSTFIELDS);

        //正式购买地址 沙盒购买地址
        $url_buy     = "https://buy.itunes.apple.com/verifyReceipt";
        $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
        
        $url = $sandbox ? $url_sandbox : $url_buy;

        //简单的curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER , false);
        curl_setopt($ch, CURLOPT_URL  , $url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$POSTFIELDS);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER ,false);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);
        $data = json_decode($response,true);
        if ($data['status']=='21007')
        {
            return $response;
        }
        if ($data['status'] != 0) {
            //throw new Exception('Invalid receipt');
            Db::table('bb_buy')->where(['uid'=>$uid,'order'=>$order])->update(['error'=>$response,'receipt'=>$receipt_data]);
            return $response;
        }
        return $response;
    }
    
    //产生订单号
    private static function get_order()
    {
         $orderSn = "PI"  .date("Ymd") . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }

}