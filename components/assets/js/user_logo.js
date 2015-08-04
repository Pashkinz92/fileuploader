var $imageCropper = null;
var ava_dlg = $('#ava-dlg');
var file_change = false;
jQuery(function ($) {

    var clicked = false;

    $('#change_logo').on('click', function(){

        if(!clicked)
        {
            clicked = true;

            if(logo_img!=''){

                var image = new Image();

                $('.preload_block', ava_dlg).addClass('load');

                image.onload = function(){

                    $('.preload_block', ava_dlg).removeClass('load');

                    if(logo_img!='' && (!logo_offset || !logo_zoom) )
                    {
                        $imageCropper = $('#image-cropper').cropit({ imageBackground: true, onImageLoaded:function(){
                            //ava_dlg.myModal('center');
                        }});

                        $imageCropper.cropit('imageSrc', logo_img).show();
                    }
                    else if(logo_img!='' && logo_offset && logo_zoom)
                    {

                        $imageCropper = $('#image-cropper').cropit({ imageBackground: true, onImageLoaded:function(){
                            if(!file_change)
                            {
                                $imageCropper.cropit('zoom', logo_zoom).cropit('offset', logo_offset ).show();
                            }
                        }});

                        $imageCropper.cropit('imageSrc', logo_img);
                    }

                };
                image.src = logo_img;

            }

            else{
                $imageCropper = $('#image-cropper').cropit({ imageBackground: true});
            }
        }

        ava_dlg.myModal({onConfirm:function(dlg, btn){

                logo_offset = $imageCropper.cropit('offset');
                logo_zoom = $imageCropper.cropit('zoom');

                lib.ajax('/fileUpload/changeUserLogo',{offset:logo_offset, zoom:logo_zoom}, function(data){
                    if(!data['ok']) return;
                    console.log(data['url']);
                    $('#ulogo img').attr('src',data['url']);
                    logo_img = data['url'];
                    ava_dlg.myModal('hide');
                });
            }
        });

    });


});

function changeUserFile(e,data){
    $imageCropper.hide();
    ava_dlg.myModal('center');
    $("#user_logo-upload-area .img_item.img_item_loaded").remove();
    var uploadErrors = [];
    $("#ava-dlg .err-msg").html("");
    var max_size = 1024*1024*2;
    for(var i=0, file; file=data.files[i]; i++)
    {
        var mime = file.type.toString().toLowerCase();
        if(file.size>(max_size) || (mime != "image/jpeg" ))
        {
            data.files.splice(i,1);
        }
        if(file.size>(max_size) ){
            uploadErrors.push('Filesize is too big');
        }
        if((mime != "image/jpeg" ))
        {
            uploadErrors.push('Not an accepted file type');
        }
    }
    if(uploadErrors.length>0){
        uploadErrors = uploadErrors.join(". ");
        $("#ava-dlg .err-msg").text(uploadErrors);
        //console.error(uploadErrors);
    }
    else{
        file_change = true;
    }

}

function userFileLoad(e, data)
{
    var file = data.result.files;
    if(!file) return;

    logo_img = null;
    logo_offset = null;
    logo_zoom = null;
    $('#user_logo-upload-area .files').html('');

    var img = new Image();
    img.onload = function(){
        $imageCropper.cropit('imageSrc', file.url).show();
        ava_dlg.myModal('center');
    };
    img.src = file.url;
    ava_dlg.data('id',file.id);
    //$('#user_logo_area').html(tmpl('img-load', file));

}