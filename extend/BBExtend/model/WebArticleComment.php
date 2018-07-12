<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;


/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
class WebArticleComment extends Model 
{
    protected $table = 'web_article_comment';
    
    public $timestamps = false;
    
    // 查关联的用户
    public function article()
    {
        // 重要说明：
        return $this->belongsTo('BBExtend\model\WebArticle', 'article_id', 'id');
    }
    
    // 查关联的评论，只是对回复而言
    public function comment()
    {
        // 重要说明：
        return $this->belongsTo('BBExtend\model\WebArticleComment', 'article_id', 'id');
    }
    
}
