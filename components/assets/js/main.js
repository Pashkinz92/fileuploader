jQuery(function ($) {
    $('.upload-area').on('click', '.del', function () {
        if ($(this).data('a')) return;
        $(this).data('a', true);

        var parent = $(this).closest('.img_item');
        var id_img = parent.data('img');
        if (!id_img || id_img == '') return;

        lib.ajax(del_img_url, {type_img: type_img, id: id_img}, function (data) {

            if (data['ok']) {
                parent.remove();
            }
            if (data['id_main'] && $('.upload-area .img_item').length )
            {
                $('.upload-area .main-image img').attr('src', $('.upload-area .img_item[data-img="' + data['id_main'] + '"]').addClass('sel').children('img').data('prev300'));
            }
        })

    });

    $('.upload-area').on('click', '.set-fav', function () {
        var self = $(this);
        if (self.data('a')) return;
        self.data('a', true);

        var parent = $(this).closest('.img_item');
        var id_img = parent.data('img');
        if (!id_img || id_img == '') return;

        lib.ajax(fav_img_url, {type_img: type_img, id: id_img}, function (data) {
            self.data('a', false);
            if (data['ok'])
            {
                $('.upload-area .sel').removeClass('sel');
                parent.addClass('sel');
                var src = $('img', parent).data('prev300');
                $('.upload-area .main-image img').attr('src', src);
            }
        })

    });

    $('.main-image').on('click', '.no_image', function () {
        $('input[name="ProductImg"]').trigger('click');
    });
});

