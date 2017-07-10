$('#Submit').on('click', function() {
    $.ajax({
        method: 'POST',
        url: url,
        data: {
            password: $('#password').val(),
            password_confirmation: $('#password_confirmation').val(),
            _token: token
        },
        success: function(msg) {
            if(msg['errors'] != null)
                alert(msg['errors']);
            else
                alert('Successfully reset password');
        },
        error: function (xmlHttpRequest, textStatus, errorThrown) {
            alert('error');
        }
        });

});