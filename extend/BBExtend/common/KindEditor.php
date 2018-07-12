<?php

/**
 * kindeditor4.1.3的php封装 
 *
 * @author 谢烨
 */
class Public_KindEditor
{
    public $version = '4.1.3'; //无用，指明版本用
    public $id = 'kindeditor_id'; //form id
    public $name = 'kindeditor_id'; //form name
    public $width = 700; //宽，单位px
    public $height = 400;//高，单位px
    public $content =''; //内容，类似<div>你好</div>
    public $formname='formtext';
    public $random =1;
    //public $form_type='{formtype:"voice"}';// 重要参数，指明了图片上传目录。
    public $form_type;
    
  //  public $css = '/css/leader/message.css';//  重要，内部css
    
    public $js_root = '/editor'; //kindedit的js目录路径
    
    public $option ="
[
        'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
        'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
        'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
        'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
        'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
        'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image', 'multiimage',
        'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
        'anchor', 'link', 'unlink', '|', 'about'
]
    ";
    
    
    //KindEditor在默认情况下自动寻找textarea所属的form元素，
    //找到form后onsubmit事件里添加sync函数，所以用form 方式提交数据，不需要手动执行sync()函数。
    
    public function __construct()
    {
        
        
    }
    
    public function esay_button()
    {
        $this->option ="
        [
    'fontname', 'fontsize', '|', 'forecolor',  'bold', 'italic', 'underline',
     '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
    'insertunorderedlist', '|', 'emoticons', 'image','flash', 'link','|',  'fullscreen']
        ";
    }
public function esay_button_source()
    {
        $this->option ="
        [
    'source', '|', 'fontname', 'fontsize', '|', 'forecolor',  'bold', 'italic', 'underline',
     '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
    'insertunorderedlist', '|', 'emoticons', 'image', 'link','|',  'fullscreen']
        ";
    }
    
    public function gethtml()
    {
       
        
        $content = Public_Html::change($this->content, 2);
        $s = <<<HTML
<script type="text/javascript" charset="utf-8" src="{$this->js_root}/kindeditor-min.js"></script>
<script type="text/javascript" src="{$this->js_root}/lang/zh_CN.js"></script>

<script type="text/javascript">
        var editor;
        KindEditor.ready(function(K) {
          var options = {
           
             themeType : 'simple',
            filterMode : true,
            newlineTag:'p',
            afterBlur: function(){this.sync();},
            allowFileManager : false,
            allowFlashUpload : false,
            extraFileUploadParams : {formtype:"{$this->form_type}",
                   random:"{$this->random}"} ,
            
            afterCreate : function() {
                    var self = this;
                    K.ctrl(document, 13, function() {
                        self.sync();
                        if (check()) {
                          K('form[name={$this->formname}]')[0].submit();
                        }
                    });
                    K.ctrl(self.edit.doc, 13, function() {
                        self.sync();
                         if (check()) {
                            K('form[name={$this->formname}]')[0].submit();
                           }
                    });
            }
            
            
          };
          editor = K.create('textarea[name="{$this->name}"]', options);

          
          
        });
</script>

       <textarea id="{$this->id}" name="{$this->name}" 
           style="width:{$this->width}px;height:{$this->height}px;">
        {$content}
       </textarea>
HTML;
        return $s;
    }
    
}
