// assets/js/frontend.js
jQuery(function ($) {
    $('.opensrs-form').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $response = $form.find('.form-response');
        const $spinner = $form.find('.spinner');

        $spinner.show();
        $response.hide().removeClass('success error');

        $.ajax({
            url: opensrsData.ajaxurl,
            type: 'POST',
            data: {
                action: 'opensrs_submit',
                nonce: opensrsData.nonce,
                data: $form.serialize()
            },
            success: function (res) {
                $response.show().addClass('success').text(res.data);
            },
            error: function (xhr) {
                const error = xhr.responseJSON?.data || 'An error occurred';
                $response.show().addClass('error').text(error);
            },
            complete: function () {
                $spinner.hide();
            }
        });
    });
});