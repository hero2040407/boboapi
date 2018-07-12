<?php
/**
 * Smarty plugin
 * @ 王丹龙
 * @ 用于判断 广告的类型 
 */
/**
 *  参数1 当前 URL 
 *  参数2 匹配文字
 * 
 */
	function smarty_modifier_ggfx( $urlname , $mchar )
	{
		return ( preg_match( $mchar , $urlname, $uarr) )  ; 
	}

?>
