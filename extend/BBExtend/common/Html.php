<?php
/**
 * 通用html函数
 * 
 * 
 * @author 谢烨
 */
class Public_Html {
    /**
     * 重要的转义函数1，用途
     * 1、主要用于拼写sql
     * 2、用于输出html
     *
     * @param {string} $s
     *            原始字符串
     * @param {int} $level
     *            级别越高(最大9，最小0)，过滤越多
     *            
     * @return {string} 转义后的字符串
     */
    public static function change($s, $level = 2) {
        if ($level == 0)
            return stripslashes ( $s ); //去除反斜杠
        elseif ($level == 1)
            return htmlspecialchars ( stripslashes ( $s ), ENT_QUOTES, 'UTF-8' ); // 把&'"<>5样转成实体
        elseif ($level == 2)
            return htmlentities ( stripslashes ( $s ), ENT_QUOTES, 'UTF-8' ); // 把所有适用的字符转成html实体格式
        elseif ($level == 3)
            return strip_tags ( stripslashes ( $s ) ); // 删除所有html和php标签
        elseif ($level == 4) // 更加彻底的删除标签和特殊字符
            return htmlentities ( strip_tags ( stripslashes ( $s ) ), ENT_QUOTES, 'UTF-8' );
        elseif ($level == 5) { // 更加彻底的删除标签和特殊字符,去除空白 , 谢烨：用于标题
            $s = preg_replace ( '/\s+/', '', $s );
            return htmlentities ( strip_tags ( stripslashes ( $s ) ), ENT_QUOTES, 'UTF-8' );
        } elseif ($level == 6) { // 用于like
            $s = preg_replace ( '/\s+/', '', stripslashes ( $s ) );
            $s = preg_replace ( '/[^\x{4e00}-\x{9fa5}0-9a-zA-Z]/u', '', $s );
            return htmlentities ( strip_tags ( $s ), ENT_QUOTES, 'UTF-8' );
        } elseif ($level == 9) { // 更加彻底的删除标签和特殊字符,只剩下[0-9a-zA-Z_]
            $s = preg_replace ( '/\W+/', '', stripslashes ( $s ) );
            return htmlentities ( strip_tags ( $s ), ENT_QUOTES, 'UTF-8' );
        } elseif ($level == 10) { // 用于描述
            $s = preg_replace ( '/\s+/', '', stripslashes ( $s ) );
            $s = htmlentities ( strip_tags ( $s ), ENT_QUOTES, 'UTF-8' );
            $s = preg_replace ( '#(&|nbsp;|amp;)#', '', $s );
            return $s;
        }
    }
    
    /**
     * 一般性的html转义，去除头尾空格
     *
     * @param string $post
     *            用户表单输入的字符串
     *            
     * @return string 转义后的字符串
     */
    public static function input($post) {
        return trim( self::change( $post, 2 ) );
    }
    
    /**
     * 富文本安全过滤，能自动将元字符转义，如< 变为 &lt;
     * 同时保持正常的html
     * 还能去除script标签。
     * 
     * @param string $html
     * @return string
     */
    public static function clean_kind_editor($html)
    {
        if(!function_exists('tidy_repair_string'))
            return trim($html);
        $conf = array(
            'output-xhtml'=>true,
            'drop-empty-paras'=>false,
            'join-classes'=>true,
            'show-body-only'=>true,
        );
        $html = tidy_repair_string($html,$conf,'UTF8');
        //过滤script
        $html = preg_replace('#script#i', '', $html);
        $html = tidy_repair_string($html,$conf,'UTF8');
        $html = trim($html);
        return  $html;
    }
    
    
    /**
     * 设置html白名单功能，除了设置的tag，其余都将删除。
     * @param unknown $html
     */
    public static function filter_tag($html)
    {
        $chain = new Zend_Filter();
        $tags = array(
            'a'      => array('href', 'target',  'style', 'title'),
            'img'    => array('src', 'alt' , 'title','align'),
            'b'      => array('style'),
            'strong' => array('style'),
            'em'     => array('style'),
            'u'      => array('style'),
            'i'      => array(),
            'ul'     => array('style'),
            'li'     => array('style'),
            'ol'     => array('style'),
            'p'      => array('style','align'),
            'br'     => array(),
            'font'   => array('size' , 'color', 'style'),
            'div'    => array('class'), //引用和回复必须有
            'span'   => array('style'),
            'center' => array(),
            'h1'     => array('style'),
            'h2'     => array('style'),
            'h3'     => array('style'),
            'h4'     => array('style'),
            'h5'     => array('style'),
            'h6'     => array('style'),
    
            'table'  => array( 'bgcolor', 'border', 'cellpadding',
                'cellspacing',), 
            'tbody'  => array( 'align', 'valign',),
            'tr'     => array( 'align', 'bgcolor', 'valign',),
            'td'     => array( 'align', 'bgcolor', 'colspan',
                'nowrap', 'rowspan', 'scope', 'valign', ),
            'th'     => array( 'align', 'bgcolor', 'colspan',
                'nowrap', 'rowspan', 'scope', 'valign',),
            'thead'  => array(),
            'object' => array('classid','width','height','id'),
            'param' => array('name','value'),
            'embed' => array(
                'type', 'width', "height", "name", "src", "allowfullscreen",
                "allowscriptaccess", "quality", "wmode", "flashvars"
            ),
    
        );
    
        $chain->addFilter(new Zend_Filter_StripTagsOld($tags));
        $html = $chain->filter($html);
        return $html;
    }
    
    
    
}//end class

