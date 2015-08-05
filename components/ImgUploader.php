<?php
    namespace fileuploader;

    use Yii;
    use yii\base\Exception;
    use yii\base\Widget;
    use yii\debug\models\search\Debug;
    use yii\helpers\Html;
    use yii\web\View;

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
        Yii::$app->assetManager->forceCopy = YII_DEBUG;
        $assets = dirname(__FILE__).'/assets';
        $baseUrl = Yii::$app->assetManager->publish($assets)[1];

        $view = Yii::$app->view;
        
        $view->registerCssFile($baseUrl.'/css/upload.css');
        $view->registerJsFile($baseUrl.'/js/vendor/jquery.ui.widget.js', ['position'=>View::POS_END,'depends'=>'yii\web\JqueryAsset']);
        //$view->registerJsFile($baseUrl.'/js/tmpl.min.js', ['position'=>View::POS_END,'depends'=>'yii\web\JqueryAsset']);
        $view->registerJsFile($baseUrl.'/js/load-image.min.js', ['position'=>View::POS_END,'depends'=>'yii\web\JqueryAsset']);
        $view->registerJsFile($baseUrl.'/js/canvas-to-blob.min.js', ['position'=>View::POS_END,'depends'=>'yii\web\JqueryAsset']);
        $view->registerJsFile($baseUrl.'/js/jquery.fileupload.js', ['position'=>View::POS_END,'depends'=>'yii\web\JqueryAsset']);
        $view->registerJsFile($baseUrl.'/js/jquery.fileupload-process.js', ['position'=>View::POS_END,'depends'=>'yii\web\JqueryAsset']);
        $view->registerJsFile($baseUrl.'/js/jquery.fileupload-image.js', ['position'=>View::POS_END,'depends'=>'yii\web\JqueryAsset']);
        $view->registerJsFile($baseUrl.'/js/jquery.fileupload-ui.js', ['position'=>View::POS_END,'depends'=>'yii\web\JqueryAsset']);

        /*if($this->params['type']=='product')
        {
            $view->registerJsFile($baseUrl.'/js/main.js', ['position'=>View::POS_END]);
            $this->plugin_config['downloadTemplateId'] = $this->params['type'].'-download';
            $this->plugin_config['uploadTemplateId'] = $this->params['type'].'-upload';

            $config = CJavaScript::encode($this->plugin_config);

            //$view->registerJsFile($baseUrl.'/js/imgUploader.js', ['position'=>View::POS_END]);
            $view->registerScript($this->params['type']."-upload", "$('#".$this->params['type']."-upload-area').fileupload($config);",CClientScript::POS_READY);

            $this->render('index', $this->params);
        }
        else */if($this->params['type']=='user_logo')
        {
            $this->plugin_config['url'] = '/fileUpload/upload-user-logo';
            $this->plugin_config['done']='js:userFileLoad';

            $this->plugin_config['change']='js:changeUserFile';

            $view->registerCssFile($baseUrl.'/cropit-master/cropit.css');
            $view->registerJsFile($baseUrl . '/cropit-master/jquery.cropit.min.js', ['position'=>View::POS_END,'depends'=>'yii\web\JqueryAsset']);
            $view->registerJsFile($baseUrl . '/js/user_logo.js', ['position'=>View::POS_END,'depends'=>'yii\web\JqueryAsset']);

            $this->plugin_config['downloadTemplateId'] = $this->params['type'] . '-download';
            $this->plugin_config['uploadTemplateId']   = $this->params['type'] . '-upload';
            $config                                    = json_encode($this->plugin_config);
            //$view->registerJsFile($baseUrl . '/js/imgUploader.js', ['position'=>View::POS_END]);
            $view->registerJs("$('#" . $this->params['type'] . "-upload-area').fileupload($config);", View::POS_READY, $this->params['type'] . "-upload");


            return $this->render('user_logo', $this->params);
        }

    }
}