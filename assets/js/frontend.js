jQuery(document).ready(function ($) {
    // Add a new SAN field when the "+" button is clicked
    $('#add-san').on('click', function (e) {
        e.preventDefault();
        var sanCount = $('#sans-fields input[name="sans[]"]').length;
        var newField = '<p><label for="sans_' + sanCount + '">SAN:</label>' +
            '<input type="text" id="sans_' + sanCount + '" name="sans[]" placeholder="Enter SAN" /></p>';
        $('#sans-fields').append(newField);
    });

    // Handle the form submission via AJAX
    $('#opensrs-form').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $('#opensrs-loading').show();
        $('#opensrs-response').empty();

        $.ajax({
            url: opensrs_ajax_obj.ajax_url,
            type: 'POST',
            data: formData + '&action=opensrs_form_submit&nonce=' + opensrs_ajax_obj.nonce,
            dataType: 'json',
            success: function (response) {
                $('#opensrs-loading').hide();
                if (response.success) {
                    $('#opensrs-response').html('<span style="color: green;">' + response.data.message + '</span>');
                } else {
                    $('#opensrs-response').html('<span style="color: red;">' + response.data.message + '</span>');
                }
            },
            error: function () {
                $('#opensrs-loading').hide();
                $('#opensrs-response').html('<span style="color: red;">An error occurred. Please try again.</span>');
            }
        });
    });
});
