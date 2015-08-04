;(function( $ ) {
    var methods = {
        init : function( options ) {
            var options = $.extend({


            }, options || {});
            return this.each(function() {

                var self= $(this);

                var using_colors = new Array();
                var number = $('.upload-area').length;

                if($('#fileupload0').length==0)
                {
                    //self.append(tmpl("template-upload-area", {number:number,'id_product':options.id_prod,color:'a'}));
                }

                //$('#fileupload'+number).fileupload(options);
                $('.upload-area').fileupload(options);



                /* ТРИГЕРЫ */

                self.on('addLine',function(e,params){

                    //console.log(using_colors);
                    // alert(params.color);

                    for(var i=0; i<using_colors.length; i++)
                    {
                        //console.log(using_colors[i].color);
                        if(using_colors[i].color==params.color)
                        {
                            if(!params.is_size)
                            {
                                using_colors[i].clicks++;
                            }
                            return;
                        }
                    }
                    using_colors.push({color:params.color,clicks:1});

                    fillColors(params.color,params.color_hex);

                    $('body').trigger('recalculate');
                    //console.log(params);
                    //alert('Добавить новую строку');
                });

                // удалили цвет
                $(self).on('removeLine',function(e,params){

                    if( !params.color ) return;

                    for(var i=0; i<using_colors.length; i++)
                    {
                        //console.log(using_colors[i].color);
                        //console.log(params.color);
                        if(using_colors[i].color==params.color)
                        {
                            console.log('Цвет,',using_colors[i].clicks);
                            if(using_colors[i].clicks>1)
                            {
                                using_colors[i].clicks--;
                            }
                            else
                            {
                                var img_arr = new Array();
                                $('.upload-area:eq('+(i)+') .img_item_loaded',self).each(function(){
                                    var id = $('input[type=hidden]',$(this)).val();
                                    if( id!='' )
                                    {
                                        img_arr.push(id);
                                    }
                                });

                                dropImage(img_arr,'',function(){
                                    $('.upload-area:eq('+(i)+')',self).remove();
                                    using_colors.splice(i,1);
                                });
                            }
                            return;
                        }
                    }
                });

                $(self).on('clearAll',function(){
                    var img_arr = new Array();
                    $('.img_item.err').remove();
                    $('.upload-area:not(:last) .img_item', self).each(function(){
                        var id = $('input',$(this)).val();
                        if( id!='' )
                        {
                            img_arr.push(id);
                        }
                    });

                    dropImage(img_arr,'',function(){
                        $('.upload-area:not(:last)',self).remove();
                    });
                    using_colors = [];

                });

                $(self).on('refreshClicks',function(){
                    using_colors = [];
                    $('.line',self).each(function(){
                        // $(this).data('color', $(this).attr('data-color') );
                        var color = $(this).data('color');
                        if( color=='0' ) return;
                        if( !color )
                        {
                            color = $('.selected_color',$(this)).attr('data-color');

                            if(!color || color=='0') return;
                        }

                        var clicks = $('#color_size_fields .color_select option[value='+color+']:selected').length;

                        using_colors.push({color: color, clicks:clicks});

                    });



                });

                // КОНЕЦ ТРИГЕРАМ

                $(self).trigger('refreshClicks');

                function fillColors(color,color_hex)
                {
                    color = color || 0;
                    color_hex = color_hex || '';
                    var text = color;

                    var vr = tmpl("template-upload-area", {number:number,'id_product':options.id_prod,color_hex:color_hex,color:color,text:text});
                    $('.line:last',self).before(vr); // добавить перед блоком без картинки
                    $('#fileupload'+number).fileupload(options);
                    number++;

                    // $('#img_loader_tmpl').tmpl({idx:100, color_hex:color_hex,text:text}).appendTo(area);
                    $('.line:not(#fileupload0):last',self).data('color',color);

                }

                function dropImage(img_arr,item_arr,callback){

                    img_arr = img_arr || [];
                    var params = {}
                    params.id_img = img_arr;
                    if(img_arr.length==0)
                    {
                        if(callback) callback();
                        return;
                    }

                    $.ajax({
                        url: "/product/delImg",
                        type: "POST",
                        data: params,
                        dataType: "json",
                        success: function(data){
                            //console.log(data);
                            if(item_arr)
                            {
                                for(var i=0; i<item_arr.length; i++)
                                {
                                    item_arr[i].closest('.img_item_loaded').remove();
                                }
                            }
                            if(callback) callback();
                            if(data['id_mainImg'])
                            {

                                $('input[type=hidden][value='+data['id_mainImg']+']', self).parent().addClass('favorite');
                                //alert(data['id_mainImg']);
                            }
                            $('body').trigger('recalculate');
                        }
                    });
                }

                self.on('click','.img_item_loaded .del',function(){
                    $('.img_item.err').remove();
                    var that = $(this);
                    var id = $('input',that.parent()).val();

                    dropImage([id],[that]);

                });


                self.on('click','.set-fav',function(){
                    $('.img_item.err').remove();
                    var item = $(this).parent();
                    var id = '';
                    if( $('#product_id').val() )
                    {
                        id = $('#product_id').val();
                    }
                    else
                    {
                        id = $.trim($(this).attr('data-prod'));
                    }
                    var id_img = $.trim( $('input',$(this).parent()).val());

                    if(id=='' || id_img=='') return;

                    var params = {id_img:id_img, id:id}

                    lib.ajax( '/product/SetFavorite', params, function(data){
                        if(data['ok']==id_img)
                        {
                            $('.img_item', self).removeClass('favorite');
                            item.addClass('favorite');
                        }

                    }, function(p1){

                    } )

                });




            });
        }

    };

    $.fn.imgUploader=function( method ){
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {

            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.imgUploader' );
        }
    };

})( jQuery );