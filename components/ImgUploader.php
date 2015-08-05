<?php
    namespace fileuploader;

    use yii\base\Exception;
    use yii\base\Widget;
    use yii\helpers\Html;

class ImgUploader extends Widget
{
    var $params = array(
            'images'=>array(),
            'user'=>null,
            'type'=>'product',
            'fileKey'=>null,
            'multiple'=>false,
            'url'=>null,
            'use_form'=>true,
    );

    var $plugin_config = array(
        'url'=> '/fileUpload/upload',
        'del_url'=> '/fileUpload/delete',
        'fav_url'=> '/fileUpload/setFav',
        //'loadImageFileTypes'=>'/^image\/(jpeg)$/',
        //'loadImageFileTypes'=>'/^image\/(jpeg)$/',
        //'loadImageFileTypes'=> '/^image\/(gif|jpeg|png)$/',
        'sequentialUploads'=> true, // загрузка по очереди
        'autoUpload' => true,
        'dropZone'=> false,
        'previewCrop'=>true,
        'pasteZone'=> false,
        'previewMaxWidth'=>'96',
        'previewMaxHeight'=>'96',
        'previewMinWidth'=>'96',
        'previewMinHeight'=>'96',

        //'finished'=>'js:function(){ $("body").trigger("file:upload"); alert(1); }',
        //'add'=>'js:fileuploadadd',
        'progress'=>'js:function(e, data){
                            if (data.context)
                            {
                                progress = parseInt(data.loaded / data.total * 100, 10); data.context.find(".status").css("width",  progress + "%");
                                if( progress==100 )
                                {
                                    data.context.find(".preload_block").addClass("load");
                                }
                            }
                        }',
        'finished'=>'js:function(){ deleteLimit(); }',
        'change'=>'js:function(e,data){
                        $(".img_item_loaded.err").remove();
                        $(".file-explorer .err-msg").html("").hide();
                        var uploadErrors = [];
                        var max_size = 1024*1024*10;
                        for(var i=0, file; file=data.files[i]; i++)
                        {
                            //console.log("file.size",file.size);
                            var mime = file.type.toString().toLowerCase();
                            if(file.size>(max_size) || (mime != "image/jpeg" ))
                            {
                                data.files.splice(i,1);
                            }
                            if(file.size>(max_size) ){
                                uploadErrors.push(\'Filesize is too big\');
                            }
                            if((mime != "image/jpeg" ))
                            {
                                uploadErrors.push(\'Not an accepted file type\');
                            }
                        }
                        if(uploadErrors.length>0)
                        {
                            $(".file-explorer .err-msg").html(uploadErrors.join("<br>")).show();
                            //console.error(uploadErrors);
                        }
                }'
    );
    
    /*public function setParams($params)
    {
        $this->params = array_merge($this->params, $params);

        if( !empty($this->params['url']) ){
            $this->plugin_config['url'] = $this->params['url'];
        }
    }*/

    public function init()
    {
        if (empty($this->params['fileKey']))
        {
            throw new Exception('Поле "fileKey" обязательное');
        }
    }

    public function run()
    {
        /*
        Yii::app()->assetManager->forceCopy = YII_DEBUG;
        $assets = dirname(__FILE__).'/assets';
        $baseUrl = Yii::app()->assetManager->publish($assets, false);

        $cs = Yii::app()->clientScript;
        $cs->registerCssFile($baseUrl.'/css/upload.css');


        $cs->registerScriptFile($baseUrl.'/js/vendor/jquery.ui.widget.js', CClientScript::POS_END);
        $cs->registerScriptFile($baseUrl.'/js/tmpl.min.js', CClientScript::POS_END);
        $cs->registerScriptFile($baseUrl.'/js/load-image.min.js', CClientScript::POS_END);
        $cs->registerScriptFile($baseUrl.'/js/canvas-to-blob.min.js', CClientScript::POS_END);
        $cs->registerScriptFile($baseUrl.'/js/jquery.fileupload.js', CClientScript::POS_END);
        $cs->registerScriptFile($baseUrl.'/js/jquery.fileupload-process.js', CClientScript::POS_END);
        $cs->registerScriptFile($baseUrl.'/js/jquery.fileupload-image.js', CClientScript::POS_END);
        $cs->registerScriptFile($baseUrl.'/js/jquery.fileupload-ui.js', CClientScript::POS_END);

        if($this->params['type']=='product')
        {
            $cs->registerScriptFile($baseUrl.'/js/main.js', CClientScript::POS_END);
            $this->plugin_config['downloadTemplateId'] = $this->params['type'].'-download';
            $this->plugin_config['uploadTemplateId'] = $this->params['type'].'-upload';

            $config = CJavaScript::encode($this->plugin_config);

            //$cs->registerScriptFile($baseUrl.'/js/imgUploader.js', CClientScript::POS_END);
            $cs->registerScript($this->params['type']."-upload", "$('#".$this->params['type']."-upload-area').fileupload($config);",CClientScript::POS_READY);

            $this->render('index', $this->params);
        }
        else if($this->params['type']=='user_logo')
        {
            $this->plugin_config['url'] = '/fileUpload/uploadUserLogo';
            $this->plugin_config['done']='js:userFileLoad';

            $this->plugin_config['change']='js:changeUserFile';

            $cs->registerCssFile($baseUrl.'/cropit-master/cropit.css');
            $cs->registerScriptFile($baseUrl . '/cropit-master/jquery.cropit.min.js', CClientScript::POS_END);
            $cs->registerScriptFile($baseUrl . '/js/user_logo.js', CClientScript::POS_END);

            $this->plugin_config['downloadTemplateId'] = $this->params['type'] . '-download';
            $this->plugin_config['uploadTemplateId']   = $this->params['type'] . '-upload';
            $config                                    = CJavaScript::encode($this->plugin_config);
            //$cs->registerScriptFile($baseUrl . '/js/imgUploader.js', CClientScript::POS_END);
            $cs->registerScript($this->params['type'] . "-upload",
                "$('#" . $this->params['type'] . "-upload-area').fileupload($config);", CClientScript::POS_READY);

            $this->render('user_logo', $this->params);
        }
        */
    }
}