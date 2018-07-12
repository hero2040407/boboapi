<?php
/**
 * Smarty plugin
 * @ 王丹龙
 * @ 用于  帖子内容 标签 加 下划线
 */
/**
 *  参数1  帖子内容 
 *  
 * 
 */
	function smarty_modifier_post_label( $posttext ,$tid )
	{
		

		$db = Sys::getdb();

		$sql = "select * from bbs_label_sort "; 
		$t_data = $db->query($sql); 

        while($row = $t_data->fetch()) {

		$posttext = str_replace( $row['label'] ,'<a class="neirong_a_span" onclick="label_tanchu(\''.$row['label'].'\',\''.urlencode($row['label']). '\', '.$tid.' )" href="#">'.$row['label'].'</a>' ,$posttext);
                   
        }

		return $posttext  ; 
	}

?>
