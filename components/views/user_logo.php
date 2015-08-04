<?php
    $set = Yii::app()->user->siteSet[User::SET_LOGO];
?>
<script>
    var logo_img = '<?= Yii::app()->user->getMyLogo(true, true);?>';
    var logo_offset = <?= ($set['offset'])?(json_encode($set['offset'], JSON_NUMERIC_CHECK)):'false';?>;
    var logo_zoom = <?= ($set['zoom'])?$set['zoom']:'false';?>;
</script>

<!-- Modal -->
<div class="mymodal " id="ava-dlg" >
    <div class="modaldlg  trans b-r-all-5">
        <div class="closedlg icon-cancel"></div>
        <div class="modalbody b-r-all-5">
            <div class="err-msg"></div>

            <div class="load_logo b-r-top-5">
                <?php $form=$this->beginWidget('CActiveForm', array(
                    'id'=>'user-logo-form',
                    'method'=>'POST',
                    'htmlOptions'=>array('enctype'=>'multipart/form-data', 'autocomplete'=>'off')

                )); ?>

                    <input type="hidden" value="<?= $type;?>" name="upload_type"/>
                    <div id="<?= $this->params['type'];?>-upload-area" class="upload-area b-r-all-5">
                        <div role="presentation" >
                            <div class="preload_block">
                                <div class="preloader"></div>
                            </div>
                            <div id="image-cropper">
                                <div class="cropit-image-preview-container">
                                    <div class="cropit-image-preview"></div>
                                    <div class="cropit-image-preview small"></div>
                                </div>

                                <div class="zoom-slider b-r-all-7">
                                    <input type="range" class="cropit-image-zoom-input custom " />
                                </div>

                            </div>
                            <div class="files clr-a"></div>
                        </div>
                        <div class="clr"></div>
                        <div class="modalfooter clr-a b-r-bottom-5">
                            <div class="file-explorer fll btn small def_btn2 b-r-all-3 fll" title="">
                                <div><?=CLib::param('LOAD_IMAGE');?></div>
                                <input type="file" name="<?= $this->params['fileKey'];?>" accept="image/*" >
                            </div>
                            <div class="btn def_btn no-border b-r-all-3 flr confirm " ><?=CLib::param('OK');?></div>
                        </div>

                    </div>
                <?php $this->endWidget(); ?>
            </div>

        </div><!-- /.body -->
        <div class="clr"></div>
    </div>
</div><!-- /.modal -->



<script id="<?= $this->params['type'];?>-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <div class="img_item b-r-all-3 fll">
        <div class='percent'></div>
        <div class="upload_gif preload_block  b-r-all-3">
            <div class='preloader'></div>
            <div class="status_bg"></div>
            <div class="status"></div>
        </div>
        <div class="preview"></div>
        <strong class="error text-danger"></strong>
    </div>
{% } %}
</script>

<script id="<?= $this->params['type'];?>-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    {% if(file.no_image) { %}
    <div class="img_item img_item_loaded err fll">
        <div class="err_msg">
            <div class='in'>
                {%=file.err%}
            </div>
        </div>
    </div>
    {% } else if(file.err_limit) { %}
        <div class="img_item img_item_loaded err fll">
            <div class="err_msg">
                <div class='in'>
                    {%=file.err%}
                </div>
            </div>
        </div>
    {% } else { %}
        <div class="img_item img_item_loaded fll "  data-img="{%=file.id%}" >

            <img src='{%=file.url%}' >

        </div>
    {% } %}
{% } %}
</script>


<script id="img-load" type="text/x-tmpl">
    <img src="{%=o.url%}" />
</script>