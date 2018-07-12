<?php

namespace BBExtend\model;

use Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;


/**
 * 成就模型类，功能多。
 */
class CommentPublicLog extends Model 
{
    protected $table = 'bb_comment_public_log';
    protected $primaryKey = "id";
    public $timestamps = false;
   
}
