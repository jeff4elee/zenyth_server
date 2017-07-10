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
            alert(msg['message']);
        },
        error: function (xmlHttpRequest, textStatus, errorThrown) {
            alert(errorThrown.toString());
            alert('error');
            alert(textStatus)
        }
        });

});