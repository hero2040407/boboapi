<?php
namespace app\shop\model;
use \Michelf\MarkdownExtra;
//use think\Model;

/**
 * 地址模型类
 * @author Administrator
 *
 */
class Dochtml
{
    const post_png ='http://resource.guaishoubobo.com/public/brandshop/post.png';
    
    private static function has_post($file)
    {
        $content = file_get_contents($file);
        if ( preg_match('#~~~\s+POST\s+~~~#is', $content) ){
            return true;
        }else {
            return false;
        }
        
    }
    
    public static function display_post($file)
    {
        $content = file_get_contents($file);
        if ( preg_match('#~~~\s+POST\s+~~~#is', $content) ){
            return ' <img class="version_img" src="' . self::post_png . '" />';
        }else {
            return '';
        }
        
    }
    
    
    public static function display_version($file,$size=60){
        $content = file_get_contents($file);
        if ( preg_match('#~~~\s*v=(\d+)\s*~~~#is', $content,$matches) ){
            $version = $matches[1];
            return self::badge_version($version,$size);
        }else {
            return '';
        }
    }
    
    
    
    
    public static function badge_version($v='5',$size){
        
        if ($v<10) {
            $css='<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
width="75" height="20">
<g shape-rendering="crispEdges">
  <path fill="#555" d="M0 0h49v20H0z"/>
  <path fill="#007ec6" d="M49 0h45v20H49z"/>
</g>
                    
<g fill="#fff" text-anchor="middle"
font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="130">
  <text x="255" y="140" transform="scale(.1)" textLength="440">release</text>
  <text x="605" y="140" transform="scale(.1)" textLength="180">v'. $v .'</text>
</g>
</svg>';
        }
        else {
            $css='<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
width="75" height="20"><g shape-rendering="crispEdges"><path fill="#555" d="M0 0h49v20H0z"/><path
fill="#007ec6" d="M49 0h45v20H49z"/></g><g fill="#fff" text-anchor="middle"
font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="110"> <text
x="255" y="140" transform="scale(.1)" textLength="390">release</text><text x="605"
y="140" transform="scale(.1)" textLength="200">v'. $v .'</text></g> </svg>';
            
        }
        $css = urlencode($css);
        $css = preg_replace('#\+#', '%20', $css);
        $s=" <img  class='version_img' width={$size} src='data:image/svg+xml;utf8,". $css ."' />";
        return  $s;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    //参数1，文件名，假设显示名懒得改，参数2可不传。
    public static function get_href($name, $display_name='',$is_del=0)
    {
        if (!$display_name) {
            $display_name = $name;
        }
        
        $style_str='';
        if($is_del) {
            $style_str=" style='text-decoration:line-through' ";
        }
        
        $doc = realpath( APP_PATH . "shop/view/doc/");
        $file = $doc . DIRECTORY_SEPARATOR .$name .".md";
        if (PHP_OS != "Linux" ) {
            $file = mb_convert_encoding($file, "GBK","UTF-8");
        }
        if (is_file($file)) {
            $new='';
            if (date('Y-m-d', filemtime($file))== date("Y-m-d") ) {
                $new =' <img src="/public/js/icon/new.gif" />';
            }elseif (date('Y-m-d', filemtime($file) )== date("Y-m-d", time() - 1*24*3600) ) {
                $new=' <img  src="/public/js/icon/yestoday.png" />';
            }
            
            $post=self::display_post($file);
            $post='';
            
            $v = self::display_version($file);
            return "<li><a {$style_str} href='/shop/doc/index/name/".urlencode($name).
            "'>{$display_name}{$post}{$v}{$new}</a>".
            
            "<font class=font3>(".date('Y-m-d', filemtime($file)) . ")</font></li>";
            
        }else {
            $doc = realpath( APP_PATH . "shop/view/doc2/");
            $file = $doc . DIRECTORY_SEPARATOR .$name .".md";
            if (PHP_OS != "Linux" ) {
                $file = mb_convert_encoding($file, "GBK","UTF-8");
            }
            if (is_file($file)) {
                $new='';
                if (date('Y-m-d', filemtime($file))== date("Y-m-d") ) {
                  //  echo 111111111111111111111111111;
                    $new =' <img src="/public/js/icon/new.gif" />';
                }elseif (date('Y-m-d', filemtime($file) )== date("Y-m-d", time() - 1*24*3600) ) {
                    $new=' <img  src="/public/js/icon/yestoday.png" />';
                }
                
                $post=self::display_post($file);
                $post='';
                $v = self::display_version($file);
                return "<li><a  {$style_str} href='/shop/doc/index2/name/".urlencode($name).
                "'>{$display_name}{$post}{$v}{$new}</a>".
                
                "<font class=font3>(".date('Y-m-d', filemtime($file)) . ")</font></li>";
                
            }else {
                return "<li>{$display_name}（暂未做）</li>";
            }
        }
    }
    
   
    
    
    public static function get_left()
    {
        //文件名，显示名
        $html ='

   <div class="ym-gbox-left">
                                    <h4>新版首页</h4>
      <ol> '.  
      self::get_href('首页接口').
    
      self::get_href('首页新闻接口').
      self::get_href('二级页面童星排行').
      self::get_href('主打童星列表').
      self::get_href('试镜卡列表').
      self::get_href('试镜卡绑定').
      self::get_href('试镜流程公告').
      self::get_href('签约童星查询').
      self::get_href('签约童星招募公告').
      
      self::get_href('通告类型列表').
      self::get_href('通告列表').
      self::get_href('通告参加流程').
      self::get_href('通告详情').
      self::get_href('通告内短视频列表').
      
      
      self::get_href('发现和星动态列表').
      self::get_href('个人动态列表').
      
      self::get_href('发现和星动态详情').
      self::get_href('动态评论和点赞').
      self::get_href('动态添加').
      self::get_href('用户权限检查').
      self::get_href('轮播图新版','轮播图新版',1).
      self::get_href('轮播图新通用接口').
      
      
      
      '
      </ol>    
             </div>


      <div class="ym-gbox-left">
                                    <h4>用户接口</h4>
      <ol>
      '.  
      self::get_href('用户详细信息', '用户详细信息',0).
      self::get_href('用户简略信息2018' ).
      self::get_href('用户列表新接口1' ).
      self::get_href('用户列表新接口2' ).
      self::get_href('用户导师列表新接口3' ).
      
      
      self::get_href('用户角色查询' ).
      self::get_href('用户详细信息编辑页面读取' ).
      self::get_href('用户搜索' ).
      self::get_href('用户资产查询' ).
      
      
      self::get_href('用户主页全部','用户主页全部',1 ).
      self::get_href('用户主页全部V2' ).
      self::get_href('用户主页视频列表' ).
      self::get_href('用户导师主页点评列表' ).
      self::get_href('用户机构主页导师列表' ).
      
      
      self::get_href('用户注册和登录','用户注册和登录',1).
      self::get_href('用户注册和登录2018').
      self::get_href('用户换临时token').
      
      self::get_href('用户只登录').
      self::get_href('用户微信网页登录').
      self::get_href('用户注册发送短信').
      self::get_href('用户手机号验证').
      
      self::get_href('用户绑定账号').
      
      
      
      self::get_href('用户签到').
      self::get_href('用户签到状态查询').
      self::get_href('用户查询手机绑定').
      
      
      self::get_href('用户接口检测昵称' ).
      self::get_href('用户接口昵称修改申请' ).
      self::get_href('定时轮询' ).
      self::get_href('用户删除订单', '用户删除订单').
      self::get_href('用户收货地址', '用户收货地址').
//       self::get_href('vip购买续费', 'vip购买续费',1).
//       self::get_href('vip购买续费价格表', 'vip购买续费价格表',1).
      self::get_href('经验值列表', '经验值列表').
      self::get_href('排行榜', '排行榜').
      self::get_href('波币使用日志').
      self::get_href('报名缴费').
      self::get_href('新用户接受注册邀请').
      self::get_href('新用户接受商户注册邀请').
      self::get_href('邀请状况查询').
      self::get_href('用户VIP条件展示').
      self::get_href('用户VIP申请支付').
      self::get_href('用户角色申请').
      self::get_href('用户VIP随机获取','用户VIP随机获取',1).
      self::get_href('用户资料随机获取').
      self::get_href('用户资料修改').
      self::get_href('用户点击完善资料').
      self::get_href('用户资料修改随机用户信息').
      
      
      
      '
      </ol>
                                </div>
                              

 <div class="ym-gbox-left">
                                    <h4>交友和拉黑</h4>
       <ol>'.  
       self::get_href('交友栏目首页').
       self::get_href('交友栏目首页视频列表').
       self::get_href('交友栏目首页感兴趣人列表','交友栏目首页感兴趣人列表',1).
       self::get_href('交友栏目VIP列表').
       self::get_href('交友栏目志同道合列表').
       
       
       
       self::get_href('我可能感兴趣的人').
       self::get_href('我可能感兴趣的人201704').
       self::get_href('关注-推荐小主播').
       self::get_href('关注-推荐课程').
       self::get_href('关注-首页视频列表').
      self::get_href('关注和取消', '关注和取消').
      self::get_href('关注对象列表201704').
      self::get_href('关注对象列表v3').
      self::get_href('关注对象列表v3_查未读视频数').
      
      self::get_href('关注对象列表', '关注对象列表').
      self::get_href('拉黑', '拉黑和取消').
      self::get_href('拉黑人列表', '拉黑对象列表').
      self::get_href('拉黑检测').
      self::get_href('举报', '举报和禁言（nodejs调用）').
      self::get_href('直播解禁' ).
      self::get_href('直播禁止' ).
      self::get_href('禁言解除' ).
      '
       </ol>
                                </div>



      <div class="ym-gbox-left">
                                    <h4>模卡接口</h4>
      <ol> '.  
      self::get_href('模卡模板列表').
      self::get_href('模卡用户个人拥有列表').
      self::get_href('模卡制作查询').
      self::get_href('模卡制作素材上传').
      
    
      '
      </ol>    
     </div>




               <div class="ym-gbox-left">
                                    <h4>成就接口</h4>
      <ol> '.  
      self::get_href('本人成就').
    
      self::get_href('开机成就弹框','开机成就弹框',1).
      self::get_href('成就明细').
      self::get_href('成就领取').
      
      '
      </ol>    
             </div>

 
              
                                <div class="ym-gbox-left">
                                    <h4>提现接口</h4>
      <ol> '.  
      self::get_href('提现历史').
      self::get_href('提现接口').
      '
      </ol>    
                                </div>
              
              
              
                                <div class="ym-gbox-left">
                                    <h4>商城接口</h4>
      <ol> '.  
      self::get_href('充值接口(安卓)', '充值接口').
      self::get_href('支付接口', '商城订单接口').
      self::get_href('app内微信支付验证').
      self::get_href('支付成功接口', '订单查询是否支付成功',1).
      
      self::get_href('商品接口', '商品接口').
      self::get_href('支付订单号含义', '订单号含义').
      self::get_href('兑换券列表').
      self::get_href('幸运转盘商品兑换').
      self::get_href('幸运转盘首页图标','幸运转盘首页图标',1).
      self::get_href('积分兑换最新交易信息').
      
      
      '
      </ol>    
                                </div>
                                
                               
                                
                                
                                <div class="ym-gbox-left">
                                    <h4>快递接口</h4>
       <ol>'.  
      self::get_href('快递接口', '快递接口').
      '                  
       </ol>
                                </div>
                                
                              
                                
                               
                                           
                                <div class="ym-gbox-left">
                                    <h4>资源接口</h4>
       <ol>'.  
       self::get_href('资源接口').
       self::get_href('底部导航图标').
       
      self::get_href('app封面广告接口').
      self::get_href('轮播图').
      self::get_href('人脸贴图').
      self::get_href('兴趣标签').
      
      
      '                  
       </ol>
                                </div>
                                <div class="ym-gbox-left">
                                  <h4>评论及回复接口</h4>
       <ol>'.  
      self::get_href('回播评论接口').
      '         
       </ol>
                                </div>
              

                              
              
              
';
        return $html;
    }
    
    
    

    public static function get_middle()
    {
        //文件名，显示名
        $html ='

                               <div class="ym-gbox">
                                    <h4>新闻头条接口</h4>
      <ol> '.  
      self::get_href('新闻头条列表').
      self::get_href('新闻详情').
      self::get_href('新闻发表评论').
      self::get_href('新闻评论列表').
      self::get_href('新闻点赞和取消').
      
    
      '
      </ol>    
                                </div>






                               <div class="ym-gbox">
                                    <h4>品牌馆接口</h4>
          <ol>'.  
          self::get_href('品牌馆列表').
          self::get_href('品牌馆详情').
          self::get_href('品牌馆导师列表').
          self::get_href('品牌馆详情视频不分页').
          self::get_href('品牌馆视频分页').
          self::get_href('品牌馆详情通告不分页').
          
          self::get_href('品牌馆通告分页','品牌馆通告分页',1).
          self::get_href('品牌馆通告分页新').
          
          self::get_href('品牌馆粉丝分页').
         
          
          
      '
         </ol>
                                </div>


                                



                                 <div class="ym-gbox">
                                    <h4>视频接口</h4>
          <ol>'.  
          self::get_href('视频全部类型').
          self::get_href('视频单独获取详情').
          self::get_href('视频回播单独获取详情').
          
          
          self::get_href('视频列表新接口').
          
      self::get_href('学啥列表接口', '秀场视频列表').
      self::get_href('购买视频接口', '购买课程接口').
      self::get_href('我的短视频列表').
      self::get_href('他人个人中心视频列表').
      self::get_href('我观看的短视频日志').
      self::get_href('直播状态').
      self::get_href('推荐页面接口').
      self::get_href('我的好友直播列表').
      self::get_href('热门-今日打榜','热门-今日打榜',1).
      self::get_href('VIP童星').
      
      self::get_href('短视频观看日志').
      self::get_href('视频详情猜你喜欢').
      self::get_href('一起玩-发现').
      self::get_href('短视频广告播放').
      self::get_href('视频点赞').
     
      
      self::get_href('视频删除短视频').
      self::get_href('视频删除回播').
      self::get_href('视频话题接口').
      
      
      '
         </ol>
                                </div>
                                


                                <div class="ym-gbox">
                                    <h4>星推官模块用户接口</h4>
          <ol>'.  
      self::get_href('视频邀请点评','视频邀请点评',1).
      self::get_href('视频邀请点评V2').
      self::get_href('视频邀请点评导师切换','视频邀请点评导师切换',1).
      self::get_href('视频邀请点评导师列表').
      self::get_href('视频单个点评','视频单个点评',1).
      self::get_href('视频邀请点评详情').
      self::get_href('视频邀请点评V2我的短视频').
      self::get_href('导师栏目首页').
      self::get_href('导师栏目首页的视频列表').
      '
         </ol>
                                </div>




                                <div class="ym-gbox">
                                    <h4>星推官模块导师接口</h4>
          <ol>'.  
          
      self::get_href('视频邀请点评抢单V2').
      self::get_href('视频导师点评').
      self::get_href('视频导师点评失败系统消息详情','视频导师点评失败系统消息详情',1).
      self::get_href('视频导师点评前查询','视频导师点评前查询',1).
      self::get_href('视频邀请点评列表','视频邀请点评列表',1).
      self::get_href('视频邀请点评列表V2').
      self::get_href('视频邀请点评列表V2_已完成').
      
      
      self::get_href('视频邀请点评_导师主页').
      self::get_href('视频邀请点评_导师偏好设置').
      self::get_href('视频邀请点评_导师偏好获取').
      self::get_href('视频邀请点评_导师排行榜').
      self::get_href('视频邀请点评_审核详情').
      self::get_href('视频导师点评_失败系统消息详情V2').
      
      
      '
         </ol>
                                </div>




                               
                                
                                
                                <div class="ym-gbox">
                                    <h4>活动和任务接口</h4>
          <ol>'.  
          self::get_href('活动和任务详细文档').
      self::get_href('玩啥列表接口', '玩啥列表接口').
      self::get_href('玩啥单个活动信息', '玩啥单个活动信息').
      self::get_href('邀约活动列表','邀约活动列表',1).
      self::get_href('邀约活动列表201704').
      self::get_href('邀约单个活动信息', '邀约单个活动信息').
      self::get_href('邀约单个活动信息h5').
      
      self::get_href('邀约活动内视频列表').
      self::get_href('邀约活动参加').
      self::get_href('邀约活动参加前查询').
      self::get_href('任务列表','任务列表',1).
      self::get_href('个人认证').
      self::get_href('任务领奖','任务领奖',1).
      self::get_href('任务状态','任务状态',1).
     
      '
         </ol>
                                </div>
                                
                                
                                <div class="ym-gbox">
                                    <h4>打赏接口</h4>
          <ol>'.  
          self::get_href('礼物列表').
      self::get_href('打赏视频价格表', '打赏视频价格表').
      self::get_href('打赏视频接口', '打赏视频接口',1).
      self::get_href('打赏视频接口_波豆', '打赏视频接口_波豆').
      self::get_href('视频打赏人排行','视频打赏人排行',1).
      self::get_href('视频打赏人排行_波豆', '视频打赏人排行_波豆').
      self::get_href('视频打赏人排行_波豆日榜', '视频打赏人排行_波豆日榜').
      self::get_href('打赏粉丝贡献榜').
      '
         </ol>
                                </div>   
              
              
                                <div class="ym-gbox">
                                  <h4>点赞接口</h4>
       <ol>'.  
      self::get_href('app点赞接口', 'app点赞接口').
      self::get_href('h5页面点赞接口', 'h5页面点赞接口').
      '         
       </ol>
                                </div>
              
                               <div class="ym-gbox">
                                    <h4>怪兽和怪兽蛋接口</h4>
          <ol>'.  
      self::get_href('全部怪兽接口', '查看所有怪兽',1).
      self::get_href('怪兽领养人数量', '怪兽的领养人数量',1).
      self::get_href('怪兽表情单个', '单个怪兽表情',1).
      self::get_href('怪兽领养人列表', '怪兽领养人列表',1).
      self::get_href('打蛋得怪兽', '打蛋得怪兽',1).
      '
         </ol>
                                </div>
              

                               <div class="ym-gbox">
                                    <h4>游戏接口</h4>
          <ol>'.  
          self::get_href('天降红包进入页面','天降红包进入页面',1).
          self::get_href('天降红包开始玩','天降红包开始玩',1).
          self::get_href('天降红包结算请求','天降红包结算请求',1).
          self::get_href('天降红包V2进入页面').
          self::get_href('天降红包V2保存记录').
          self::get_href('天降红包V2历史排行榜').
          
          self::get_href('天降红包V2奖励node调用').
          
          
          
      '
         </ol>
                                </div>

                ';
        return $html;
    }
    
    
    public static function get_right()
    {
        //文件名，显示名
        $html ='
<h4>数据字典</h4>
       <ol>
              <li><a target="_blank" href="/systemmanage/dict/index">数据字典</a></li>
              <li><a target="_blank" href="/systemmanage/dict/index/type/race">大赛数据字典</a></li>
              <li><a target="_blank" href="/systemmanage/tool/api">全部 api 检索</a></li>
              '.  
              self::get_href('服务器api修改日志').
              self::get_href('mysql常用命令').
              
      '
             <li><a target="_blank" href="http://192.168.31.210:3000/project/45/interface/api">后台api文档</a></li>
       </ol>


                 <h4>大赛</h4>
       <ol>
                
                
                '.  
                self::get_href('大赛报名').
                self::get_href('大赛个人分享页面').
                self::get_href('大赛个人分享投票').
                self::get_href('大赛人气榜单').
                
                
                self::get_href('大赛微信报名').
                self::get_href('大赛权限管理').
                
                
                self::get_href('大赛报名状态','大赛报名状态',1).
                self::get_href('大赛报名状态新').
                self::get_href('大赛赛区选择和个人信息').
                
                
      self::get_href('大赛报名支付下单').
      self::get_href('大赛传档案').
      self::get_href('大赛报名支付接口').
      self::get_href('大赛首页直播列表').
      self::get_href('大赛首页大赛列表','大赛首页大赛列表',1).
      self::get_href('大赛首页大赛列表新').
      
      self::get_href('大赛单个详情','大赛单个详情',1).
      self::get_href('大赛单个详情新').
      self::get_href('大赛单个详情h5').
      
      self::get_href('大赛问答公告').
      self::get_href('大赛参赛排行榜视频').
      self::get_href('大赛参赛热门视频','大赛参赛热门视频',1).
      self::get_href('大赛参赛热门视频201706').
      self::get_href('大赛演示视频').
      self::get_href('大赛叫号系统').
      self::get_href('大赛第三方代理').
      
      '
       </ol>
        
        
<h4>h5页面</h4>
       <ol>'.  
      self::get_href('机构信息展示').
      
      '
       </ol>

    
              <h4>错误代码</h4>
       <ol>'.  
       self::get_href('错误代码', '错误代码').
       self::get_href('新后台错误代码').
       
       self::get_href( '客户端错误日志列表和添加' ).
      
      '
       </ol>
              
              
              
                        
                          <h4>配置接口</h4>
       <ol>'.  
       self::get_href('全局通用配置').
      self::get_href('多配置版本', '多配置版本号读取').
      self::get_href('举报配置接口', '举报配置接口').
      self::get_href('ios配置接口', 'ios特殊配置接口').
      self::get_href('安卓客户端版本号接口', '安卓客户端版本号接口').
  //    self::get_href('阿里云文件保存路径').
      
      '
       </ol>
                        
                        
                                    <h4>其它接口</h4>
       <ol>'.   
       self::get_href('官方问答').
       self::get_href('首页弹框').
      self::get_href('redis按行删除接口', 'redis按行删除接口').
      self::get_href('直播聊天记录').
      self::get_href('伪直播开启').
      self::get_href('伪直播关闭').
      self::get_href('增加视频观看').
      self::get_href('增加粉').
      self::get_href('增加用户粉').
      self::get_href('增加新闻观看').
      self::get_href('增加动态观看数').
      
      self::get_href('短视频生成').
      self::get_href('活动排名确认').
      self::get_href('后台注册用户').
      
      
      self::get_href('redis键统计').
      
      self::get_href('php-redis').
      '
       </ol>
                        
                        <h4>签名验证方案(v2)</h4>
       <ol>'.  
      self::get_href('token方案', '签名验证方案').
      self::get_href('签名校验方案接口列表', '签名校验方案接口列表').
      '
       </ol>
                        
                        
                       <h4>消息说明</h4>
       <ol>'.  
      self::get_href('系统消息').
      self::get_href('系统消息用户回复').
      self::get_href('得到用户推送配置','得到用户推送配置',1).
      self::get_href('设置用户推送配置','设置用户推送配置',1).
      self::get_href('新版用户消息推送配置').
      
      
      self::get_href('nodejs消息说明').
      self::get_href('直播关闭广播消息字段').
      self::get_href('微信公众号访问凭证').
      
      '
       </ol>
              
              
                ';
        return $html;
    }
    
    
    
    
}