<?php if($use_form){ ?>
<form action="/" method="post">
<?php } ?>

    <script>
        var del_img_url = '<?=$this->plugin_config['del_url'];?>';
        var fav_img_url = '<?=$this->plugin_config['fav_url'];?>';
        var type_img = '<?=$type;?>';
    </script>

    <?php if(!empty($id_item)) { ?>
        <input type="hidden" value="<?= $id_item;?>" name="id_item"/>
    <?php } ?>
    <input type="hidden" value="<?= $type;?>" name="upload_type"/>


    <div id="<?= $this->params['type'];?>-upload-area" class="upload-area clr-a">
        <div class="clr-a fll">
            <table class="exp">
                <tr>
                    <td class="main-image">

                        <div class="img b-r-all-3">
                            <?php if($images && count($images)) {
                                foreach($images as $img)  { ?>
                                    <?php if($main_image==$img['id'])
                                    {
                                        echo  CLib::getImage($img_folder_key, array('id_user'=>$id_user, 'id_item'=>$id_item, 'img_url'=>$img['img_url'] ), 300  );
                                        break;
                                    }
                                 }
                            }
                            else
                            {
                                echo CLib::getDefaultImg();
                            } ?>
                        </div>
                        <div class="img_caption">
                            <?=CLib::param('MAIN_IMAGE'); ?>
                        </div>
                    </td>
                    <td>
                        <div class="file-explorer " title="">
                            <div class="note"><?=CLib::param('PRODUCT_IMAGE_RECOMENDS') ?></div>

                            <span class="in">
                                <?//=CLib::param('ADD_MORE_PHOTO');?>
                                <input type="file" <?= ($this->params['multiple']?'multiple':'');?> name="<?= $this->params['fileKey'];?>" >
                            </span>
                            <div class="clr"></div>
                            <div class="err-msg"></div>
                            <div class="clr"></div>
                            <div class="presentationImgs" role="presentation">
                                <div class="files clr-a">
                                    <?php if($images && count($images)) {
                                        foreach($images as $img)  { ?>
                                            <div class="img_item img_item_loaded b-r-all-3 fll <?= ($main_image==$img['id'])?'sel':'';?> "  data-img="<?= $img['id'];?>">
                                                <div class="set-fav icon-check trans" title="<?= CLib::param('DO_MAIN');?>"></div>
                                                <div class="del icon-cancel1 trans" title="<?= CLib::param('DELETE');?>"></div>
                                                <img src="<?= CLib::getImageUrl($img_folder_key, array('id_user'=>$id_user, 'id_item'=>$id_item, 'img_url'=>$img['img_url'] ), 100  );?>" data-prev100="<?= CLib::getImageUrl($img_folder_key, array('id_user'=>$id_user, 'id_item'=>$id_item, 'img_url'=>$img['img_url'] ), 100  );?>" data-prev300="<?= CLib::getImageUrl($img_folder_key, array('id_user'=>$id_user, 'id_item'=>$id_item, 'img_url'=>$img['img_url'] ), 300  );?>" >
                                            </div>
                                        <?php }
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>




        <div class="clr"></div>

    </div>
<?php if($use_form){ ?>
</form>
<?php } ?>
<div class="clr"></div>

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
    {% /* console.log(file.time); */ if(file.no_image) { %}
    <div class="img_item img_item_loaded err fll">
        <div class="err_msg">
            <div class='in'>
                {%=file.err%}
            </div>
        </div>
    </div>
    {% } else if(file.err_limit) {  %}
        <div class="img_item img_item_loaded err fll">
            <div class="err_msg">
                <div class='in'>
                    {%=file.err%}
                </div>
            </div>
        </div>
    {%  } else { %}
    {% if(parseInt(file.id_main)==parseInt(file.id)){
        var src = file.thumbUrl_300;
        $('.upload-area .main-image img').attr('src', src);
    } %}
        <div class="img_item img_item_loaded b-r-all-3 fll {%= (parseInt(file.id_main)==parseInt(file.id))?'sel':''%} "  data-img="{%=file.id%}"  >
            <div class="set-fav icon-check trans" title="<?= CLib::param('DO_MAIN');?>"></div>
            <div class="del icon-cancel1 trans" title="<?= CLib::param('DELETE');?>"></div>
            <img src='{%=file.thumbUrl%}' class='b-r-all-3' data-prev100='{%=file.thumbUrl_100%}' data-prev300='{%=file.thumbUrl_300%}' >
        </div>
    {% } %}
{% }  %}
</script>